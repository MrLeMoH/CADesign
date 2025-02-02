<?php

namespace App\Http\Controllers;


use App\Models\Book;
use Illuminate\Http\Request;
use App\Models\Author;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
class ReservationController extends Controller
{
    public function set(Request $request)
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
            ], 422); // HTTP статус 422 — Unprocessable Entity
        }

        $BookId = $request->input('id');

        $query = Book::with('author');
        $query->where('id', "=", $BookId);

        return response()->json($query->get());
    }
    public function set2(Request $request)
    {
        return "asda";
    }
}
