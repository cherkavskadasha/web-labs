<?php

namespace App\Http\Controllers;

use App\Models\Reader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ReaderController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function getReaders(): JsonResponse
    {
        $readers = Reader::all();

        return response()->json($readers, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function getReaderById(string $id): JsonResponse
    {
        $reader = Reader::find($id);
        if (!$reader) {
            return response()->json(['error' => 'Reader not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($reader, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function createReader(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $reader = DB::transaction(function () use ($data) {
            $reader = new Reader();
            $reader->full_name = $data['full_name'] ?? '';
            $reader->email = $data['email'] ?? '';
            $reader->save();

            return $reader;
        });

        return response()->json($reader, Response::HTTP_CREATED);
    }

    /**
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function updateReaderById(string $id, Request $request): JsonResponse
    {
        $reader = Reader::find($id);
        if (!$reader) {
            return response()->json(['error' => 'Reader not found'], Response::HTTP_NOT_FOUND);
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

        return response()->json($reader, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function deleteReaderById(string $id): JsonResponse
    {
        $reader = Reader::find($id);
        if (!$reader) {
            return response()->json(['error' => 'Reader not found'], Response::HTTP_NOT_FOUND);
        }

        $reader->delete();

        return response()->json(['message' => 'Reader deleted successfully'], Response::HTTP_OK);
    }
}