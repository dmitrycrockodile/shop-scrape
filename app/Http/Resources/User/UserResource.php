<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Retailer\RetailerResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    /**
     * @OA\Schema(
     *     schema="UserResource",
     *     type="object",
     *     description="User resource schema",
     *     @OA\Property(property="id", type="integer", example=1, description="ID of the user"),
     *     @OA\Property(property="name", type="string", example="John Doe", description="Name of the user"),
     *     @OA\Property(property="email", type="string", example="johndoe@example.com", description="Email address of the user"),
     *     @OA\Property(property="is_verified", type="boolean", example=true, description="Whether the user's email is verified"),
     *     @OA\Property(property="role", type="string", example="REGULAR_USER", description="Role of the user"),
     *     @OA\Property(property="location", type="string", example="New York", description="Location of the user"),
     *     @OA\Property(
     *         property="retailers",
     *         type="array",
     *         description="List of retailers associated with the user",
     *         @OA\Items(ref="#/components/schemas/RetailerResource")
     *     )
     * )
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_verified' => !!$this->email_verified_at,
            'role' => $this->role->text(),
            'admin' => $this->isSuperUser(),
            'location' => $this->location,
            'retailers' => RetailerResource::collection($this->accessibleRetailers()->get())
        ];
    }
}
