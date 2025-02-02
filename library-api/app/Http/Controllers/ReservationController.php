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
    public function create(Request $request): JsonResponse
    {
        $BookId = $request->json('book_id', null);
        if (empty($BookId)) {
            return response()->json(['error' => 'Bad Request id is empty'], Response::HTTP_BAD_REQUEST);
        }

        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $book = Book::find($BookId);
        if (!$book) {
            return response()->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        // Проверяем, доступна ли книга
        if ($book->is_available == 0) {
            return response()->json(['error' => 'Book is not available'], Response::HTTP_BAD_REQUEST);
        }

        // Создаем бронирование
        $reservation = Reservation::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'reserved_at' => now(),
        ]);

        // Обновляем статус книги, что она больше не доступна
        $book->is_available = 0;
        $book->save();

        return response()->json([
            'message' => 'Book successfully reserved',
            'reservation' => $reservation
        ], Response::HTTP_CREATED);
    }

    public function returnBook(Request $request): JsonResponse
    {
        // Получаем текущего пользователя
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Получаем параметры book_id и user_id из запроса
        $bookId = $request->json('book_id');
        if (!$bookId) {
            return response()->json(['error' => 'Bad Request: book_id is required'], Response::HTTP_BAD_REQUEST);
        }

        // Находим бронирование по user_id и book_id
        $reservation = Reservation::where('user_id', $user->id)
            ->where('book_id', $bookId)
            ->whereNull('returned_at') // Проверяем, что книга еще не возвращена
            ->first();

        if (!$reservation) {
            return response()->json(['error' => 'Reservation not found or already returned'], Response::HTTP_NOT_FOUND);
        }

        // Находим книгу
        $book = $reservation->book;
        if (!$book) {
            return response()->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        // Обновляем статус книги, что она доступна
        $book->is_available = 1;
        $book->save();

        // Обновляем дату возврата в бронировании
        $reservation->returned_at = now();
        $reservation->save();

        return response()->json([
            'message' => 'Book successfully returned',
            'reservation' => $reservation
        ], Response::HTTP_OK);
    }


}
