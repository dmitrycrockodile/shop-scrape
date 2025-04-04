<?php

namespace App\Service;

use App\Models\User;
use App\Http\Resources\PackSize\PackSizeResource;
use App\Models\PackSize;
use Illuminate\Support\Facades\DB;

class PackSizeService
{
    /**
     * Store a new pack size.
     *
     * @param array $data
     *
     * @return array
     */
    public function store(array $data, User $user): array
    {
        DB::beginTransaction();

        $packSize = PackSize::create($data);

        $user->packSizes()->attach($packSize->id);

        DB::commit();

        return $this->successResponse($packSize);
    }

    /**
     * Success response formatting.
     *
     * @param PackSize $packSize
     *
     * @return array
     */
    private function successResponse(PackSize $packSize): array
    {
        return [
            'success' => true,
            'packSize' => new PackSizeResource($packSize)
        ];
    }
}