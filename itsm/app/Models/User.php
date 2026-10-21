<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use SoftDeletes;
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'full_name', 'email', 'password', 'phone', 'department',
        'position', 'avatar', 'role', 'source', 'source_id', 'company_id', 'is_active',
        'employee_number', 'job_title', // <-- Menambahkan full_name agar diizinkan di-update oleh Sync
    ];

    public function getNameAttribute($value): string
    {
        return !empty($this->attributes['full_name'])
            ? $this->attributes['full_name']
            : ($value ?? 'User');
    }

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTechnician(): bool
    {
        return $this->role === 'technician';
    }

    public function ticketsCreated()
    {
        return $this->hasMany(Ticket::class, 'requester_id');
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function ticketsAssigned()
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'assigned_to');
    }

    public function ratings()
    {
        return $this->hasMany(TicketRating::class, 'technician_id');
    }

    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    /**
     * Resolve company name string for display when the relation is not loaded.
     * Named getCompanyNameAttribute to avoid conflicting with the company() relation.
     */
    public function getCompanyNameAttribute(): string
    {
        // Use loaded relation first
        if ($this->relationLoaded('company') && $this->getRelation('company')) {
            return $this->getRelation('company')->name;
        }

        if ($this->company_id) {
            $company = \App\Models\Company::find($this->company_id);
            if ($company) return $company->name;
        }

        $email = strtolower($this->email ?? '');
        $department = strtolower($this->department ?? '');

        if (str_contains($email, 'caraka') || str_contains($department, 'caraka')) {
            return \App\Models\Company::find(2)?->name ?? 'PT Caraka Tirta Pratama';
        }

        if (str_contains($email, '@amarinshipmgmt.com') || str_contains($email, '@amarinshipmanagement.com') || str_contains($email, 'itoperation') || str_contains($email, 'head.it')) {
            return \App\Models\Company::find(1)?->name ?? 'PT Amarin Ship Management';
        }

        return \App\Models\Company::find(3)?->name ?? 'PT Amarin Crewing Services';
    }

    /**
     * Check if user has a given role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
}