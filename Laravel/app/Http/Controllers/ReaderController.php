<?php

namespace App\Http\Controllers;

use App\Models\Reader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ReaderController extends Controller
{
    public const FIELDS = [
        'id'        => 'EQUAL',
        'full_name' => 'LIKE',
        'email'     => 'LIKE',
    ];

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getReaders(Request $request): JsonResponse
    {
        $query = Reader::query();

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
        $readers = $query->paginate($itemsPerPage);

        return response()->json($readers, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function getReaderById(string $id): JsonResponse
    {
        $reader = Reader::find($id);

        if (empty($reader)) {
            return response()->json(
                ['message' => 'Reader not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json($reader, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createReader(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $reader = DB::transaction(function () use ($data) {
            $reader = new Reader();
            $reader->fill($data);
            $reader->save();

            return $reader;
        });

        return new JsonResponse($reader, Response::HTTP_CREATED);
    }

    /**
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateReaderById(string $id, Request $request): JsonResponse
    {
        $reader = Reader::find($id);

        if (empty($reader)) {
            return response()->json(
                ['message' => 'Reader not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $data = json_decode($request->getContent(), true);

        DB::transaction(function () use ($reader, $data) {
            if (isset($data['full_name'])) {
                $reader->full_name = $data['full_name'];
            }
            if (isset($data['email'])) {
                $reader->email = $data['email'];
            }
            $reader->save();
        });

        return new JsonResponse($reader, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function deleteReaderById(string $id): JsonResponse
    {
        $reader = Reader::find($id);

        if (empty($reader)) {
            return response()->json(
                ['message' => 'Reader not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $reader->delete();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}