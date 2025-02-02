<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Models\Author;
use Illuminate\Http\JsonResponse;

class BookController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $title = $request->query('title', null);
        $author = $request->query('author', null);
        $BookId = $request->query('id', null);
        $perPage = $request->query('per_page', 10); // Количество записей на странице

        $query = Book::with('author');

        if (!empty($title)) {
            $query->where('title', 'like', "%$title%");
        }
        if (!empty($author)) {
            $query->whereHas('author', function ($q) use ($author) {
                $q->where('name', 'like', "%$author%");
            });
        }
        if (!empty($BookId)) {
            $query->where('id', "=", $BookId);
        }

        return response()->json($query->paginate($perPage));
    }
}
