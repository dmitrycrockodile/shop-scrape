<?php

namespace App\Http\Resources\Retailer;

use App\Http\Resources\Currency\CurrencyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RetailerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    /**
     * @OA\Schema(
     *     schema="RetailerResource",
     *     type="object",
     *     description="Retailer resource schema",
     *     @OA\Property(property="id", type="integer", example=1, description="ID of the retailer"),
     *     @OA\Property(property="title", type="string", example="Best Buy", description="Title or name of the retailer"),
     *     @OA\Property(property="url", type="string", example="http://www.bestbuy.com", description="URL of the retailer"),
     *     @OA\Property(property="logo", type="string", example="http://www.bestbuy.com/logo.png", description="Logo URL of the retailer"),
     *     @OA\Property(
     *         property="currency",
     *         ref="#/components/schemas/CurrencyResource",
     *         description="Currency information for the retailer"
     *     )
     * )
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'logo' => $this->logo,
            'currency' => new CurrencyResource($this->currency),
        ];
    }
}
