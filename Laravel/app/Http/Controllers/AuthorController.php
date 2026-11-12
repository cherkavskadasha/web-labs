<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthorController extends Controller
{
    public const FIELDS = [
        'id'   => 'EQUAL',
        'name' => 'LIKE',
        'bio'  => 'LIKE',
    ];

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAuthors(Request $request): JsonResponse
    {
        $query = Author::query();

        foreach (self::FIELDS as $key => $type) {
            if ($request->filled($key)) {
                if ($type === 'LIKE') {
                    $query->where($key, 'like', '%' . $request->query($key) . '%');
                } elseif ($type === 'EQUAL') {
                    $query->where($key, $request->query($key));
                }
            }
        }

        $itemsPerPage = (int) $request->query('itemsPerPage', 10);
        $authors = $query->paginate($itemsPerPage);

        return response()->json($authors, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function getAuthorById(string $id): JsonResponse
    {
        $author = Author::find($id);

        if (empty($author)) {
            return response()->json(
                ['message' => 'Author not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json($author, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createAuthor(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $author = DB::transaction(function () use ($data) {
            $author = new Author();
            $author->fill($data);
            $author->save();

            return $author;
        });

        return new JsonResponse($author, Response::HTTP_CREATED);
    }

    /**
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAuthorById(string $id, Request $request): JsonResponse
    {
        $author = Author::find($id);

        if (empty($author)) {
            return response()->json(
                ['message' => 'Author not found'],
                Response::HTTP_NOT_FOUND
            );
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

        return new JsonResponse($author, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function deleteAuthorById(string $id): JsonResponse
    {
        $author = Author::find($id);

        if (empty($author)) {
            return response()->json(
                ['message' => 'Author not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $author->delete();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}