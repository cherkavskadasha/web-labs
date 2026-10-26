<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    private static array $products = [
        ['id' => 1, 'title' => 'Laptop', 'price' => 1500],
        ['id' => 2, 'title' => 'Phone', 'price' => 1000],
    ];

    private function findProductIndex(int $id): ?int
    {
        foreach (self::$products as $index => $product) {
            if ($product['id'] === $id) {
                return $index;
            }
        }
        return null;
    }

    // 1. READ ALL
    #[Route('/products', name: 'product_list', methods: ['GET'])]
    public function getProducts(): JsonResponse
    {
        return $this->json(self::$products, Response::HTTP_OK);
    }

    // 2. READ ONE 
    #[Route('/products/{id}', name: 'product_item', methods: ['GET'])]
    public function getProductItem(int $id): JsonResponse
    {
        $index = $this->findProductIndex($id);

        if ($index === null) {
            return $this->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(self::$products[$index], Response::HTTP_OK);
    }

    // 3. CREATE 
    #[Route('/products', name: 'product_create', methods: ['POST'])]
    public function createProduct(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true) ?? [];

        if (empty($requestData['title']) || empty($requestData['price'])) {
            return $this->json([
                'error' => 'Validation failed: title and price are required'
            ], Response::HTTP_BAD_REQUEST); 
        }

        $newProduct = [
            'id' => rand(3, 1000),
            'title' => $requestData['title'],
            'price' => $requestData['price'],
        ];

        return $this->json([
            'message' => 'Product created successfully',
            'product' => $newProduct
        ], Response::HTTP_CREATED);
    }

    // 4. UPDATE 
    #[Route('/products/{id}', name: 'product_update', methods: ['PUT'])]
    public function updateProduct(int $id, Request $request): JsonResponse
    {
        $index = $this->findProductIndex($id);

        if ($index === null) {
            return $this->json(['error' => "Product with ID {$id} not found"], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true) ?? [];

        return $this->json([
            'message' => "Product {$id} updated successfully",
            'updatedData' => $requestData
        ], Response::HTTP_OK);
    }

    // 5. DELETE 
    #[Route('/products/{id}', name: 'product_delete', methods: ['DELETE'])]
    public function deleteProduct(int $id): JsonResponse
    {
        $index = $this->findProductIndex($id);

        if ($index === null) {
            return $this->json(['error' => "Product with ID {$id} not found"], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'message' => "Product {$id} deleted successfully"
        ], Response::HTTP_OK);
    }
}