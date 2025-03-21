<?php

namespace App\Http\Resources\PackSize;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackSizeResource extends JsonResource
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
            'name' => $this->name,
            'weight' => "{$this->weight} {$this->weight_unit}",
            'amount' => $this->amount
        ];
    }
}
