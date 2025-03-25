<?php

namespace App\Http\Resources\Currency;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    /**
     * @OA\Schema(
     *     schema="CurrencyResource",
     *     type="object",
     *     description="Currency resource schema",
     *     @OA\Property(property="id", type="integer", example=1, description="ID of the currency"),
     *     @OA\Property(property="code", type="string", example="USD", description="Currency code"),
     *     @OA\Property(property="name", type="string", example="US Dollar", description="Currency name"),
     *     @OA\Property(property="symbol", type="string", example="$", description="Currency symbol")
     * )
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'symbol' => $this->symbol
        ];
    }
}
