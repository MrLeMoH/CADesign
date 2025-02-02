<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Models\Author;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ReservationController extends Controller
{
    public function set(Request $request): JsonResponse
    {
        // Валидация параметра 'id' (должен быть числом и обязательным)
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:books,id', // проверяем, что id существует в таблице books
        ]);

        if ($validator->fails()) {
            // Возвращаем ошибку с деталями валидации
            return response()->json([
                'error' => 'Invalid data',
                'messages' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $BookId = $request->input('id');

        $query = Book::with('author');
        $query->where('id', "=", $BookId);

        return response()->json($query->get());
    }
}
