<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
    public const FIELDS = [
        'id'   => 'EQUAL',
        'name' => 'LIKE',
    ];

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getCategories(Request $request): JsonResponse
    {
        $query = Category::query();

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
        $categories = $query->paginate($itemsPerPage);

        return response()->json($categories, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function getCategoryById(string $id): JsonResponse
    {
        $category = Category::find($id);

        if (empty($category)) {
            return response()->json(
                ['message' => 'Category not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json($category, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createCategory(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $category = DB::transaction(function () use ($data) {
            $category = new Category();
            $category->fill($data);
            $category->save();

            return $category;
        });

        return new JsonResponse($category, Response::HTTP_CREATED);
    }

    /**
     * @param string $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateCategoryById(string $id, Request $request): JsonResponse
    {
        $category = Category::find($id);

        if (empty($category)) {
            return response()->json(
                ['message' => 'Category not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $data = json_decode($request->getContent(), true);

        DB::transaction(function () use ($category, $data) {
            if (isset($data['name'])) {
                $category->name = $data['name'];
            }
            $category->save();
        });

        return new JsonResponse($category, Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    public function deleteCategoryById(string $id): JsonResponse
    {
        $category = Category::find($id);

        if (empty($category)) {
            return response()->json(
                ['message' => 'Category not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $category->delete();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}