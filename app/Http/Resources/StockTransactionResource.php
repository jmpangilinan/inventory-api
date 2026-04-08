<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StockTransaction
 */
class StockTransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'reason' => $this->reason->value,
            'quantity' => $this->quantity,
            'stock_before' => $this->stock_before,
            'stock_after' => $this->stock_after,
            'notes' => $this->notes,
            'product' => new ProductResource($this->whenLoaded('product')),
            'performed_by' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
        ];
    }
}
