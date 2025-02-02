<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Models\Author;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BookController extends Controller
{
    // Метод для поиска книг с возможностью фильтрации по названию, автору и ID
    public function search(Request $request): JsonResponse
    {
        // Получение параметров запроса, с дефолтными значениями
        $title = $request->query('title', null);
        $author = $request->query('author', null);
        $BookId = $request->query('id', null);
        $perPage = $request->query('per_page', 10); // Количество записей на странице

        // Стартуем запрос для книги с загрузкой данных автора
        $query = Book::with('author');

        // Фильтрация по названию книги, если оно указано
        if (!empty($title)) {
            $query->where('title', 'like', "%$title%");
        }

        // Фильтрация по автору, если указано имя автора
        if (!empty($author)) {
            // Используем whereHas для фильтрации через связь 'author'
            $query->whereHas('author', function ($q) use ($author) {
                $q->where('name', 'like', "%$author%");
            });
        }

        // Фильтрация по ID книги, если оно указано
        if (!empty($BookId)) {
            $query->where('id', "=", $BookId);
        }

        // Получаем результат с пагинацией
        $response = $query->paginate($perPage);

        // Если результат пустой, возвращаем ошибку с кодом 404
        if ($response->isEmpty()) {
            return response()->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        // Возвращаем результат с пагинацией
        return response()->json($query->paginate($perPage));
    }

    // Метод для удаления книги
    public function delete(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        // Проверка авторизации: если пользователя нет или он не администратор
        if (!$user || $user->is_admin != 1) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Поиск книги по ID
        $book = Book::find($id);

        // Если книга не найдена, возвращаем ошибку 404
        if (!$book) {
            return response()->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        // Удаляем книгу
        $book->delete();

        return response()->json(['message' => 'Book deleted successfully']);
    }

    // Метод для редактирования книги
    public function edit(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        // Проверка авторизации: если пользователя нет или он не администратор
        if (!$user || $user->is_admin != 1) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Поиск книги по ID
        $book = Book::find($id);

        // Если книга не найдена, возвращаем ошибку 404
        if (!$book) {
            return response()->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        // Валидируем данные из запроса
        $validatedData = $request->validate([
            'title' => 'string|max:255',
            'author_id' => 'integer|exists:authors,id', // Проверка существования автора
            'publication_year' => 'integer|min:1000|max:' . date('Y'), // Проверка валидности года
            'is_available' => 'boolean' // Проверка булевого значения для доступности книги
        ]);

        // Обновляем книгу с валидированными данными
        $book->update($validatedData);

        // Возвращаем успешный ответ с обновленными данными книги
        return response()->json(['message' => 'Book updated successfully', 'book' => $book]);
    }

    // Метод для создания новой книги
    public function create(Request $request): JsonResponse
    {
        $user = $request->user();

        // Проверка авторизации: если пользователя нет или он не администратор
        if (!$user || $user->is_admin != 1) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Валидируем данные из запроса
        $validatedData = $request->validate([
            'title' => 'required|string|max:255', // Название книги обязательно
            'author_id' => 'required|integer|exists:authors,id', // Обязателен существующий автор
            'publication_year' => 'required|integer|min:1000|max:' . date('Y'), // Год публикации обязателен
            'is_available' => 'boolean' // Проверка булевого значения для доступности книги
        ]);

        // Создаем новую книгу с валидированными данными
        $book = Book::create($validatedData);

        // Возвращаем успешный ответ с созданной книгой
        return response()->json(['message' => 'Book created successfully', 'book' => $book], Response::HTTP_CREATED);
    }

}
