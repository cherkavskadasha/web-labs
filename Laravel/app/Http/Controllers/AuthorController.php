<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthorController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function getAuthors(): JsonResponse
    {
        $authors = Author::with('books')->get();

        return response()->json($authors, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function getAuthorById(string $id): JsonResponse
    {
        $author = Author::with('books')->find($id);
        if (!$author) {
            return response()->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($author, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function createAuthor(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $author = DB::transaction(function () use ($data) {
            $author = new Author();
            $author->name = $data['name'] ?? '';
            $author->bio = $data['bio'] ?? null;
            $author->save();

            return $author;
        });

        return response()->json($author, Response::HTTP_CREATED);
    }

    /**
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function updateAuthorById(string $id, Request $request): JsonResponse
    {
        $author = Author::find($id);
        if (!$author) {
            return response()->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        DB::transaction(function () use ($author, $data) {
            if (isset($data['name'])) {
                $author->name = $data['name'];
            }
            if (array_key_exists('bio', $data)) {
                $author->bio = $data['bio'];
            }
            $author->save();
        });

        return response()->json($author, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function deleteAuthorById(string $id): JsonResponse
    {
        $author = Author::find($id);
        if (!$author) {
            return response()->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $author->delete();

        return response()->json(['message' => 'Author deleted successfully'], Response::HTTP_OK);
    }
}