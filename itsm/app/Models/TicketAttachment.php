<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAttachment extends Model
{
    // Tambahkan 'ticket_comment_id' di sini
    protected $fillable = ['ticket_id', 'ticket_comment_id', 'user_id', 'filename', 'original_name', 'mime_type', 'size', 'path'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comment(): BelongsTo
{
    return $this->belongsTo(TicketComment::class, 'ticket_comment_id');
}
}