<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->foreignId('category_id')->constrained();
            $table->foreignId('sub_category_id')->nullable()->constrained();
            $table->foreignId('priority_id')->constrained();
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('assigned_by')->nullable()->constrained('users');
            $table->enum('status', ['open', 'assigned', 'in_progress', 'pending', 'resolved', 'closed', 'cancelled'])->default('open');
            $table->enum('impact', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->enum('urgency', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->enum('type', ['incident', 'service_request', 'problem', 'change_request'])->default('incident');
            $table->string('location')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->boolean('sla_breached')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
