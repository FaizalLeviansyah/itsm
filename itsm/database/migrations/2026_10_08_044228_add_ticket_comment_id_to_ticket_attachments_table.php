<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            // Menambahkan kolom relasi ke komentar (nullable karena lampiran utama tiket tidak punya comment_id)
            $table->foreignId('ticket_comment_id')->nullable()->after('ticket_id')->constrained('ticket_comments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            $table->dropForeign(['ticket_comment_id']);
            $table->dropColumn('ticket_comment_id');
        });
    }
};