<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class BookController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function getBooks(): JsonResponse
    {
        $books = Book::with(['author', 'category'])->get();

        return response()->json($books, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function getBookById(string $id): JsonResponse
    {
        $book = Book::with(['author', 'category'])->find($id);
        if (!$book) {
            return response()->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($book, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function createBook(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $book = DB::transaction(function () use ($data) {
            $book = new Book();
            $book->title = $data['title'] ?? '';
            $book->author_id = $data['author_id'] ?? null;
            $book->category_id = $data['category_id'] ?? null;
            $book->published_year = (int) ($data['published_year'] ?? 0);
            $book->save();

            return $book;
        });

        return response()->json($book, Response::HTTP_CREATED);
    }

    /**
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function updateBookById(string $id, Request $request): JsonResponse
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        DB::transaction(function () use ($book, $data) {
            if (isset($data['title'])) {
                $book->title = $data['title'];
            }
            if (array_key_exists('author_id', $data)) {
                $book->author_id = $data['author_id'];
            }
            if (array_key_exists('category_id', $data)) {
                $book->category_id = $data['category_id'];
            }
            if (isset($data['published_year'])) {
                $book->published_year = (int) $data['published_year'];
            }
            $book->save();
        });

        return response()->json($book, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function deleteBookById(string $id): JsonResponse
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $book->delete();

        return response()->json(['message' => 'Book deleted successfully'], Response::HTTP_OK);
    }
}