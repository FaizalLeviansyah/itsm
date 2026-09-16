<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('roles', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique(); // Contoh: admin, technician, staff
        $table->string('display_name'); // Contoh: Administrator, IT Support Technician
        $table->timestamps();
    });

    // Tambahkan foreign key role_id ke tabel users (atau sesuaikan jika tabel users sudah ada)
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'role_id')) {
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
