<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\GetProductsRequest;
use App\Http\Requests\StoreProductRequest;
use App\Helpers\ApiResponse;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class ProductController extends Controller implements HasMiddleware
{


    public static function middleware()
    {
        return [
            new Middleware(PermissionMiddleware::using('view_products'), only: ['index', 'show', 'options']),
            new Middleware(PermissionMiddleware::using('create_products'), only: ['store']),
            new Middleware(PermissionMiddleware::using('edit_products'), only: ['update']),
            new Middleware(PermissionMiddleware::using('delete_products'), only: ['destroy']),


        ];
    }




    /**
     * Display a listing of the resource.
     */
    public function index(GetProductsRequest $request)
    {
        $products = Product::with('category')->search($request->search)->latest()
            ->paginate($request->input('limit', 15));

        return ApiResponse::success(
            new PaginatedResource($products, ProductResource::class),
            'Products retrieved successfully.'
        );
    }



    public function options(GetProductsRequest $request)
    {
        $products = Product::select(
            'id',
            'name',
            'price',
            'image',
            'stock'
        )
            ->search($request->search)
            ->latest()
            ->get();

        return ApiResponse::success(
            ProductResource::collection($products),
            'Products options retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return ApiResponse::success(
            new ProductResource($product->load('category')),
            'Product created successfully.',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return ApiResponse::error(
                'Product not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        return ApiResponse::success(
            new ProductResource($product),
            'Product retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::error(
                'Product not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        $product->update($request->validated());

        return ApiResponse::success(
            new ProductResource($product->load('category')),
            'Product updated successfully.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ApiResponse::error(
                'Product not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        if ($product->image) {
            Storage::delete($product->image);
        }

        $product->delete();

        return ApiResponse::success(
            null,
            'Product deleted successfully.'
        );
    }
}
