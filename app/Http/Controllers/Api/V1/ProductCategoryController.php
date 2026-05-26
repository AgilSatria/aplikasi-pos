<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use App\Http\Requests\GetProductCategoriesRequest;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Helpers\ApiResponse;
use App\Http\Resources\PaginatedResource;
use App\Http\Resources\ProductCategoryResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateProductCategoryRequest;

use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

use Spatie\Permission\Middleware\PermissionMiddleware;


class ProductCategoryController extends Controller implements HasMiddleware
{


    public static function middleware()
    {
        return [
            new Middleware(PermissionMiddleware::using('view_product_categories'), only: ['index', 'show', 'options']),
            new Middleware(PermissionMiddleware::using('create_product_categories'), only: ['store']),
            new Middleware(PermissionMiddleware::using('edit_product_categories'), only: ['update']),
            new Middleware(PermissionMiddleware::using('delete_product_categories'), only: ['destroy']),


        ];
    }


    /**
     * Display a listing of the resource.
     */
    public function index(GetProductCategoriesRequest $request)
    {
        $categories = ProductCategory::search($request->search)->latest()
            ->paginate($request->input('limit', 15));

        return ApiResponse::success(
            new PaginatedResource($categories, ProductCategoryResource::class),
            'Product categories retrieved successfully.'
        );
    }



    public function options(GetProductCategoriesRequest $request)

    {
        $categories = ProductCategory::select('id', 'name')
            ->search($request->search)
            ->latest()
            ->get();

        return ApiResponse::success(
            ProductCategoryResource::collection($categories),
            'Product categories options retrieved successfully.'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductCategoryRequest $request)
    {
        $category = ProductCategory::create($request->validated());

        return ApiResponse::success(
            new ProductCategoryResource($category),
            'Product category created successfully.',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return ApiResponse::error(
                'Product category not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        return ApiResponse::success(
            new ProductCategoryResource($category),
            'Product category retrieved successfully.'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductCategoryRequest $request, string $id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return ApiResponse::error(
                'Product category not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        $category->update($request->validated());

        return ApiResponse::success(
            new ProductCategoryResource($category),
            'Product category updated successfully.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = ProductCategory::find($id);

        if (!$category) {
            return ApiResponse::error(
                'Product category not found.',
                Response::HTTP_NOT_FOUND
            );
        }

        if ($category->image) {
            Storage::delete($category->image);
        }

        $category->delete();

        return ApiResponse::success(
            null,
            'Product category deleted successfully.'
        );
    }
}
