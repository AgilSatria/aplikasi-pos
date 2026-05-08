<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TransactionItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'product_image_url' => $this->whenLoaded('product', function () {
                return $this->product->image ? asset(Storage::url($this->product->image)) : null;
            }),
            'price' => (float) (string)$this->price,
            'quantity' => $this->quantity,
            'subtotal' => (float) (string)$this->subtotal,
        ];
    }
}
