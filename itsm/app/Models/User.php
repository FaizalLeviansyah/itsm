<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'department',
        'position', 'avatar', 'role', 'source', 'source_id', 'company_id', 'is_active',
        'employee_number', 'job_title', // <-- Tambahan dari API
    ];

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

    public function company()
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

    public function getCompanyAttribute()
{
    // Jika relasi company sudah ada, ambil datanya
    if ($this->relationLoaded('company') && $this->getRelation('company')) {
        return $this->getRelation('company');
    }
    
    if ($this->company_id) {
        $company = \App\Models\Company::find($this->company_id);
        if ($company) return $company;
    }

    // --- AUTO DETEKSI OTOMATIS BERDASARKAN EMAIL/KONDISI ---
    $email = strtolower($this->email ?? '');
    if (str_contains($email, 'amarin') || str_contains($email, 'itoperation')) {
        return \App\Models\Company::find(1) ?? (object)['name' => 'PT Amarin Ship Management'];
    }

    // Default fallback jika tidak satupun cocok
    return \App\Models\Company::first() ?? (object)['name' => 'PT Amarin Ship Management'];
}
}