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
    /**
     * @OA\Schema(
     *     schema="PackSizeResource",
     *     type="object",
     *     description="Pack Size resource schema",
     *     @OA\Property(property="id", type="integer", example=1, description="ID of the pack size"),
     *     @OA\Property(property="name", type="string", example="Small", description="Name of the pack size"),
     *     @OA\Property(property="weight", type="string", example="500 g", description="Weight of the pack size with unit"),
     *     @OA\Property(property="amount", type="integer", example=10, description="Amount of items in the pack")
     * )
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
