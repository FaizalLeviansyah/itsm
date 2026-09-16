<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('justification')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->enum('approval_level', ['manager', 'it_head', 'director'])->default('manager');
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        Schema::create('escalation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('priority_id')->constrained();
            $table->integer('escalation_minutes');
            $table->foreignId('escalate_to')->constrained('users');
            $table->integer('level')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ticket_escalations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('escalated_to')->constrained('users');
            $table->foreignId('escalated_from')->nullable()->constrained('users');
            $table->integer('level');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_escalations');
        Schema::dropIfExists('escalation_rules');
        Schema::dropIfExists('ticket_approvals');
    }
};
