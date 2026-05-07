<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests\UploadProductCategoryImageRequest;

use App\Helpers\ApiResponse;
use App\Http\Resources\ProductCategoryResource;
use Illuminate\Support\Facades\Storage;

class ProductCategoryImageController extends Controller
{
    public function store(UploadProductCategoryImageRequest $request, string $id)
    {
        // Handle image upload for the product category
        $category = ProductCategory::findOrFail($id);

        if (!$category) {
            return ApiResponse::error(
                'Product category not found.',
                Response::HTTP_NOT_FOUND
            );
        }


        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }


        $path = $request->file('image')->store('product_categories', 'public');

        $category->update(['image' => $path]);

        return ApiResponse::success(
            new ProductCategoryResource($category),
            'Image updated successfully.'
        );
    }
}
