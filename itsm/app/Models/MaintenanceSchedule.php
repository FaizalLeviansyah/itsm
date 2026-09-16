<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceSchedule extends Model
{
    protected $fillable = [
        'title', 'description', 'asset_id', 'assigned_to', 'company_id',
        'frequency', 'next_due_date', 'last_performed_at', 'auto_create_ticket', 'is_active',
    ];

    protected $casts = [
        'next_due_date' => 'date',
        'last_performed_at' => 'date',
        'auto_create_ticket' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }

    public function calculateNextDueDate(): void
    {
        $this->next_due_date = match ($this->frequency) {
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'semi_annual' => now()->addMonths(6),
            'annual' => now()->addYear(),
        };
        $this->save();
    }
}
