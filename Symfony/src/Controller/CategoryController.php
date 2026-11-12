<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/categories')]
class CategoryController extends AbstractController
{
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;

    /**
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', methods: ['GET'])]
    public function getCategories(Request $request): JsonResponse
    {
        $queryParams = $request->query->all();
        $categories = $this->categoryRepository->getCategories($queryParams);

        return $this->json($categories, Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['GET'])]
    public function getCategoryById(int $id): JsonResponse
    {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            return $this->json(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($category, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', methods: ['POST'])]
    public function createCategory(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $category = new Category();
        $category->setName($data['name'] ?? '');

        $this->categoryRepository->save($category, true);

        return $this->json($category, Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['PATCH'])]
    public function updateCategory(int $id, Request $request): JsonResponse
    {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            return $this->json(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $category->setName($data['name']);
        }

        $this->categoryRepository->save($category, true);

        return $this->json($category, Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['DELETE'])]
    public function deleteCategory(int $id): JsonResponse
    {
        $category = $this->categoryRepository->find($id);
        if (!$category) {
            return $this->json(['error' => 'Category not found'], Response::HTTP_NOT_FOUND);
        }

        $this->categoryRepository->remove($category, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}