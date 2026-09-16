<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_audits', function (Blueprint $table) {
            $table->id();
            $table->string('audit_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('auditor_id')->constrained('users');
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->date('scheduled_date');
            $table->date('completed_date')->nullable();
            $table->string('location')->nullable();
            $table->string('vessel_name')->nullable();
            $table->integer('total_assets')->default(0);
            $table->integer('found_assets')->default(0);
            $table->integer('missing_assets')->default(0);
            $table->integer('damaged_assets')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_audit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_audit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained();
            $table->enum('condition', ['good', 'fair', 'poor', 'damaged', 'missing'])->default('good');
            $table->enum('scan_status', ['pending', 'scanned', 'manual'])->default('pending');
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->foreignId('scanned_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_audit_items');
        Schema::dropIfExists('asset_audits');
    }
};
