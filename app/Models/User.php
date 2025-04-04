<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'location'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $attributes = [
        'role' => UserRole::REGULAR_USER->value,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class
        ];
    }

    public function isSuperUser(): bool
    {
        return $this->role === UserRole::SUPER_USER;
    }

    public function isRegularUser(): bool
    {
        return $this->role === UserRole::REGULAR_USER;
    }

    public function retailers(): BelongsToMany
    {
        return $this->belongsToMany(Retailer::class, 'user_retailers', 'user_id', 'retailer_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'user_products', 'user_id', 'product_id');
    }

    public function packSizes(): BelongsToMany
    {
        return $this->belongsToMany(PackSize::class, 'user_pack_sizes', 'user_id', 'pack_size_id');
    }

    public function scopeAccessibleRetailers()
    {
        return $this->isSuperUser()
            ? Retailer::query()
            : $this->retailers();
    }
}
