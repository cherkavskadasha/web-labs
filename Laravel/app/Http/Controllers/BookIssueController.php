<?php

namespace App\Http\Controllers;

use App\Models\BookIssue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class BookIssueController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function getBookIssues(): JsonResponse
    {
        $bookIssues = BookIssue::with(['book', 'reader'])->get();

        return response()->json($bookIssues, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function getBookIssueById(string $id): JsonResponse
    {
        $bookIssue = BookIssue::with(['book', 'reader'])->find($id);
        if (!$bookIssue) {
            return response()->json(['error' => 'Book Issue not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($bookIssue, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function createBookIssue(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $bookIssue = DB::transaction(function () use ($data) {
            $bookIssue = new BookIssue();
            $bookIssue->book_id = $data['book_id'] ?? null;
            $bookIssue->reader_id = $data['reader_id'] ?? null;
            $bookIssue->issue_date = $data['issue_date'] ?? date('Y-m-d');
            $bookIssue->return_date = $data['return_date'] ?? null;
            $bookIssue->save();

            return $bookIssue;
        });

        return response()->json($bookIssue, Response::HTTP_CREATED);
    }

    /**
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function updateBookIssueById(string $id, Request $request): JsonResponse
    {
        $bookIssue = BookIssue::find($id);
        if (!$bookIssue) {
            return response()->json(['error' => 'Book Issue not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        DB::transaction(function () use ($bookIssue, $data) {
            if (array_key_exists('book_id', $data)) {
                $bookIssue->book_id = $data['book_id'];
            }
            if (array_key_exists('reader_id', $data)) {
                $bookIssue->reader_id = $data['reader_id'];
            }
            if (isset($data['issue_date'])) {
                $bookIssue->issue_date = $data['issue_date'];
            }
            if (array_key_exists('return_date', $data)) {
                $bookIssue->return_date = $data['return_date'];
            }
            $bookIssue->save();
        });

        return response()->json($bookIssue, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function deleteBookIssueById(string $id): JsonResponse
    {
        $bookIssue = BookIssue::find($id);
        if (!$bookIssue) {
            return response()->json(['error' => 'Book Issue not found'], Response::HTTP_NOT_FOUND);
        }

        $bookIssue->delete();

        return response()->json(['message' => 'Book Issue deleted successfully'], Response::HTTP_OK);
    }
}