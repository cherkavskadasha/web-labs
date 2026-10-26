<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    private static array $products = [
        ['id' => 1, 'title' => 'Laptop', 'price' => 1500],
        ['id' => 2, 'title' => 'Phone', 'price' => 1000],
    ];

    private function findProductIndex($id): ?int
    {
        foreach (self::$products as $index => $product) {
            if ($product['id'] == $id) {
                return $index;
            }
        }
        return null;
    }

    // 1. READ ALL 
    public function getProducts()
    {
        return response()->json(self::$products, Response::HTTP_OK);
    }

    // 2. READ ONE 
    public function getProductItem($id)
    {
        $index = $this->findProductIndex($id);

        if ($index === null) {
            return response()->json(['error' => "Product with ID {$id} not found"], Response::HTTP_NOT_FOUND);
        }

        return response()->json(self::$products[$index], Response::HTTP_OK);
    }

    // 3. CREATE 
    public function createProduct(Request $request)
    {
        $requestData = json_decode($request->getContent(), true) ?? [];

        if (empty($requestData['title']) || empty($requestData['price'])) {
            return response()->json([
                'error' => 'Validation failed: title and price are required'
            ], Response::HTTP_BAD_REQUEST); // 400 Bad Request
        }

        $newProduct = [
            'id' => rand(3, 1000),
            'title' => $requestData['title'],
            'price' => $requestData['price'],
        ];

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $newProduct
        ], Response::HTTP_CREATED);
    }

    // 4. UPDATE 
    public function updateProduct($id, Request $request)
    {
        $index = $this->findProductIndex($id);

        if ($index === null) {
            return response()->json(['error' => "Product with ID {$id} not found"], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true) ?? [];

        return response()->json([
            'message' => "Product {$id} updated successfully",
            'updatedData' => $requestData
        ], Response::HTTP_OK);
    }

    // 5. DELETE 
    public function deleteProduct($id)
    {
        $index = $this->findProductIndex($id);

        if ($index === null) {
            return response()->json(['error' => "Product with ID {$id} not found"], Response::HTTP_NOT_FOUND);
        }

        return response()->noContent();
    }
}