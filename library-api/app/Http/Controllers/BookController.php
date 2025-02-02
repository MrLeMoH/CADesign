<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Models\Author;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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

        $response = $query->paginate($perPage);

        if ($response->isEmpty()) {
            return response()->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($query->paginate($perPage));
    }


    public function delete(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->is_admin) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $book = Book::find($id);

        if (!$book) {
            return response()->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $book->delete();

        return response()->json(['message' => 'Book deleted successfully']);
    }

    public function edit(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->is_admin) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $book = Book::find($id);

        if (!$book) {
            return response()->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $validatedData = $request->validate([
            'title' => 'string|max:255',
            'author_id' => 'integer|exists:authors,id',
            'publication_year' => 'integer|min:1000|max:' . date('Y'),
            'is_available' => 'boolean'
        ]);

        $book->update($validatedData);

        return response()->json(['message' => 'Book updated successfully', 'book' => $book]);
    }

    public function create(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->is_admin) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'author_id' => 'required|integer|exists:authors,id',
            'publication_year' => 'required|integer|min:1000|max:' . date('Y'),
            'is_available' => 'boolean'
        ]);

        $book = Book::create($validatedData);
        return response()->json(['message' => 'Book created successfully', 'book' => $book], Response::HTTP_CREATED);
    }

}
