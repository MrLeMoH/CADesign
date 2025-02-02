<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Models\Author;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Reservation;

class ReservationController extends Controller
{
    // Метод для создания бронирования
    public function create(Request $request): JsonResponse
    {
        // Получаем ID книги из запроса, если оно пустое — возвращаем ошибку 400
        $BookId = $request->json('book_id', null);
        if (empty($BookId)) {
            return response()->json(['error' => 'Bad Request id is empty'], Response::HTTP_BAD_REQUEST);
        }

        // Получаем текущего пользователя
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Находим книгу по ID, если не нашли — возвращаем ошибку 404
        $book = Book::find($BookId);
        if (!$book) {
            return response()->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        // Проверяем, доступна ли книга для бронирования
        if ($book->is_available == 0) {
            return response()->json(['error' => 'Book is not available'], Response::HTTP_BAD_REQUEST);
        }

        // Создаем запись о бронировании
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'reserved_at' => now(), // Записываем текущее время как время бронирования
        ]);

        // Обновляем статус книги: она больше не доступна для других пользователей
        $book->is_available = 0;
        $book->save();

        return response()->json([
            'message' => 'Book successfully reserved',
            'reservation' => $reservation
        ], Response::HTTP_CREATED);
    }

    // Метод для возврата книги
    public function returnBook(Request $request): JsonResponse
    {
        // Получаем текущего пользователя
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Получаем параметр book_id из запроса
        $bookId = $request->json('book_id');
        if (!$bookId) {
            return response()->json(['error' => 'Bad Request: book_id is required'], Response::HTTP_BAD_REQUEST);
        }

        // Ищем активное бронирование для этого пользователя и книги
        // Проверяем, что книга еще не возвращена (поля 'returned_at' нет)
        $reservation = Reservation::where('user_id', $user->id)
            ->where('book_id', $bookId)
            ->whereNull('returned_at') // Фильтруем только те бронирования, которые еще не возвращены
            ->first();

        // Если бронирование не найдено или уже было возвращено, возвращаем ошибку
        if (!$reservation) {
            return response()->json(['error' => 'Reservation not found or already returned'], Response::HTTP_NOT_FOUND);
        }

        // Находим книгу через бронирование
        $book = $reservation->book;
        if (!$book) {
            return response()->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        // Обновляем статус книги, чтобы она стала доступной
        $book->is_available = 1;
        $book->save();

        // Обновляем дату возврата в бронировании
        $reservation->returned_at = now(); // Устанавливаем текущую дату как дату возврата
        $reservation->save();

        return response()->json([
            'message' => 'Book successfully returned',
            'reservation' => $reservation
        ], Response::HTTP_OK);
    }
}
