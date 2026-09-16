<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name', 'code', 'full_name', 'logo', 'address',
        'phone', 'email', 'website', 'asset_prefix', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return null;
    }

    public function generateAssetTag(): string
    {
        $prefix = $this->asset_prefix ?: 'AST';
        $last = Asset::where('company_id', $this->id)->withTrashed()->count() + 1;
        return sprintf('%s-%s-%05d', $prefix, $this->code, $last);
    }
}
