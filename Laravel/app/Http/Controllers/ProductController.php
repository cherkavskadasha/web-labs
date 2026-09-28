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

    public function getProducts()
    {
        return response()->json(self::$products, Response::HTTP_OK);
    }

    public function getProductItem($id)
    {
        foreach (self::$products as $product) {
            if ($product['id'] == $id) {
                return response()->json($product, Response::HTTP_OK);
            }
        }

        return response()->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
    }

    public function createProduct(Request $request)
    {
        $requestData = json_decode($request->getContent(), true) ?? [];

        $newProduct = [
            'id' => rand(3, 1000),
            'title' => $requestData['title'] ?? 'Default Product',
            'price' => $requestData['price'] ?? 0,
        ];

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $newProduct
        ], Response::HTTP_CREATED);
    }

    public function updateProduct($id, Request $request)
    {
        $requestData = json_decode($request->getContent(), true) ?? [];

        return response()->json([
            'message' => "Product {$id} updated successfully",
            'updatedData' => $requestData
        ], Response::HTTP_OK);
    }

    public function deleteProduct($id)
    {
        return response()->json([
            'message' => "Product {$id} deleted successfully"
        ], Response::HTTP_OK);
    }
}
