<?php

namespace App\Http\Controllers;

use App\Models\BookIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BookIssueController extends Controller
{
    public const FIELDS = [
        'id'          => 'EQUAL',
        'book_id'     => 'EQUAL',
        'reader_id'   => 'EQUAL',
        'issue_date'  => 'BETWEEN',
        'return_date' => 'BETWEEN',
    ];

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getBookIssues(Request $request): JsonResponse
    {
        $query = BookIssue::with(['book', 'reader']);

        foreach (self::FIELDS as $key => $type) {
            if (!$request->filled($key)) {
                continue;
            }

            $value = $request->query($key);

            if ($type === 'EQUAL') {
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
        $issues = $query->paginate($itemsPerPage);

        return response()->json($issues, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function getBookIssueById(string $id): JsonResponse
    {
        $issue = BookIssue::with(['book', 'reader'])->find($id);

        if (empty($issue)) {
            return response()->json(
                ['message' => 'Book Issue not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json($issue, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createBookIssue(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $issue = DB::transaction(function () use ($data) {
            $issue = new BookIssue();
            $issue->fill($data);
            $issue->save();

            return $issue;
        });

        return new JsonResponse($issue->load(['book', 'reader']), Response::HTTP_CREATED);
    }

    /**
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateBookIssueById(string $id, Request $request): JsonResponse
    {
        $issue = BookIssue::find($id);

        if (empty($issue)) {
            return response()->json(
                ['message' => 'Book Issue not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $data = json_decode($request->getContent(), true);

        DB::transaction(function () use ($issue, $data) {
            if (isset($data['book_id'])) {
                $issue->book_id = $data['book_id'];
            }
            if (isset($data['reader_id'])) {
                $issue->reader_id = $data['reader_id'];
            }
            if (isset($data['issue_date'])) {
                $issue->issue_date = $data['issue_date'];
            }
            if (array_key_exists('return_date', $data)) {
                $issue->return_date = $data['return_date'];
            }
            $issue->save();
        });

        return new JsonResponse($issue->load(['book', 'reader']), Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function deleteBookIssueById(string $id): JsonResponse
    {
        $issue = BookIssue::find($id);

        if (empty($issue)) {
            return response()->json(
                ['message' => 'Book Issue not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $issue->delete();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}