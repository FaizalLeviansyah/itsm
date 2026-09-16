<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_number', 'title', 'description', 'category_id', 'sub_category_id',
        'priority_id', 'requester_id', 'assigned_to', 'assigned_by', 'status',
        'impact', 'urgency', 'type', 'location', 'vessel_name', 'vessel_id',
        'company_id', 'due_date', 'first_response_at', 'resolved_at', 'closed_at',
        'assigned_at', 'resolution_notes', 'sla_breached',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'assigned_at' => 'datetime',
        'sla_breached' => 'boolean',
    ];

    public static function generateTicketNumber(): string
    {
        $prefix = 'TKT';
        $date = now()->format('Ymd');
        $last = static::withTrashed()->whereDate('created_at', today())->lockForUpdate()->count() + 1;
        return sprintf('%s-%s-%04d', $prefix, $date, $last);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TicketHistory::class)->orderByDesc('created_at');
    }

    public function rating(): HasOne
    {
        return $this->hasOne(TicketRating::class);
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class)->withTimestamps();
    }

    public function approval(): HasOne
    {
        return $this->hasOne(TicketApproval::class);
    }

    public function escalations(): HasMany
    {
        return $this->hasMany(TicketEscalation::class)->orderByDesc('created_at');
    }

    public function getResolutionTimeAttribute()
    {
        if ($this->assigned_at && $this->resolved_at) {
            return $this->assigned_at->diffInMinutes($this->resolved_at);
        }
        return null;
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->due_date && !in_array($this->status, ['resolved', 'closed'])) {
            return now()->greaterThan($this->due_date);
        }
        return false;
    }
}