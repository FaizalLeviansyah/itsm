<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('technician_id')->constrained('users');
            $table->unsignedTinyInteger('rating'); // 1-5 stars
            $table->unsignedTinyInteger('response_rating')->nullable(); // Rating for response time
            $table->unsignedTinyInteger('resolution_rating')->nullable(); // Rating for resolution quality
            $table->unsignedTinyInteger('professionalism_rating')->nullable(); // Rating for professionalism
            $table->text('feedback')->nullable();
            $table->integer('resolution_time_minutes')->nullable(); // Time from assigned to resolved
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_ratings');
    }
};
