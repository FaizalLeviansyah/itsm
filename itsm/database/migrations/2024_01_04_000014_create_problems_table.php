<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('problems', function (Blueprint $table) {
            $table->id();
            $table->string('problem_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->text('root_cause')->nullable();
            $table->text('workaround')->nullable();
            $table->text('solution')->nullable();
            $table->foreignId('category_id')->constrained();
            $table->foreignId('priority_id')->constrained();
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['open', 'investigating', 'known_error', 'resolved', 'closed'])->default('open');
            $table->enum('impact', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->timestamp('identified_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->integer('affected_incidents')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Link problems to tickets (incidents)
        Schema::create('problem_ticket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('problem_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Service Catalog
        Schema::create('service_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->foreignId('category_id')->constrained();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('icon')->nullable();
            $table->integer('sla_hours')->default(24);
            $table->enum('approval_required', ['none', 'manager', 'it_head'])->default('none');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_catalog');
        Schema::dropIfExists('problem_ticket');
        Schema::dropIfExists('problems');
    }
};
