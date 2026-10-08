<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketComment extends Model
{
    protected $fillable = ['ticket_id', 'user_id', 'comment', 'is_internal'];

    protected $casts = ['is_internal' => 'boolean'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

        public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }

        public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'ticket_comment_id');
    }
}
