<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BookController extends Controller
{
    public const FIELDS = [
        'id'             => 'EQUAL',
        'title'          => 'LIKE',
        'published_year' => 'BETWEEN',
        'author_id'      => 'EQUAL',
        'category_id'    => 'EQUAL',
    ];

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getBooks(Request $request): JsonResponse
    {
        $query = Book::with(['author', 'category']);

        foreach (self::FIELDS as $key => $type) {
            if (!$request->filled($key)) {
                continue;
            }

            $value = $request->query($key);

            if ($type === 'LIKE') {
                $query->where($key, 'like', '%' . $value . '%');
            } elseif ($type === 'EQUAL') {
                $query->where($key, $value);
            } elseif ($type === 'BETWEEN') {
                if (is_array($value)) {
                    if (!empty($value['gte'])) {
                        $query->where($key, '>=', $value['gte']);
                    }
                    if (!empty($value['lte'])) {
                        $query->where($key, '<=', $value['lte']);
                    }
                } else {
                    $query->where($key, $value);
                }
            }
        }

        $itemsPerPage = (int) $request->query('itemsPerPage', 10);
        $books = $query->paginate($itemsPerPage);

        return response()->json($books, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function getBookById(string $id): JsonResponse
    {
        $book = Book::with(['author', 'category'])->find($id);

        if (empty($book)) {
            return response()->json(
                ['message' => 'Book not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json($book, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createBook(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $book = DB::transaction(function () use ($data) {
            $book = new Book();
            $book->fill($data);
            $book->save();

            return $book;
        });

        return new JsonResponse($book->load(['author', 'category']), Response::HTTP_CREATED);
    }

    /**
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateBookById(string $id, Request $request): JsonResponse
    {
        $book = Book::find($id);

        if (empty($book)) {
            return response()->json(
                ['message' => 'Book not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $data = json_decode($request->getContent(), true);

        DB::transaction(function () use ($book, $data) {
            if (isset($data['title'])) {
                $book->title = $data['title'];
            }
            if (isset($data['published_year'])) {
                $book->published_year = $data['published_year'];
            }
            if (isset($data['author_id'])) {
                $book->author_id = $data['author_id'];
            }
            if (isset($data['category_id'])) {
                $book->category_id = $data['category_id'];
            }
            $book->save();
        });

        return new JsonResponse($book->load(['author', 'category']), Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function deleteBookById(string $id): JsonResponse
    {
        $book = Book::find($id);

        if (empty($book)) {
            return response()->json(
                ['message' => 'Book not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $book->delete();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}