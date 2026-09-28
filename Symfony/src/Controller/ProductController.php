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

    #[Route('/products', name: 'product_list', methods: ['GET'])]
    public function getProducts(): JsonResponse
    {
        return $this->json(self::$products, Response::HTTP_OK);
    }

    #[Route('/products/{id}', name: 'product_item', methods: ['GET'])]
    public function getProductItem(int $id): JsonResponse
    {
        foreach (self::$products as $product) {
            if ($product['id'] === $id) {
                return $this->json($product, Response::HTTP_OK);
            }
        }

        return $this->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/products', name: 'product_create', methods: ['POST'])]
    public function createProduct(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true) ?? [];
        
        $newProduct = [
            'id' => rand(3, 1000),
            'title' => $requestData['title'] ?? 'Default Product',
            'price' => $requestData['price'] ?? 0,
        ];

        return $this->json([
            'message' => 'Product created successfully',
            'product' => $newProduct
        ], Response::HTTP_CREATED);
    }

    #[Route('/products/{id}', name: 'product_update', methods: ['PUT'])]
    public function updateProduct(int $id, Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true) ?? [];

        return $this->json([
            'message' => "Product {$id} updated successfully",
            'updatedData' => $requestData
        ], Response::HTTP_OK);
    }

    #[Route('/products/{id}', name: 'product_delete', methods: ['DELETE'])]
    public function deleteProduct(int $id): JsonResponse
    {
        return $this->json([
            'message' => "Product {$id} deleted successfully"
        ], Response::HTTP_OK);
    }
}