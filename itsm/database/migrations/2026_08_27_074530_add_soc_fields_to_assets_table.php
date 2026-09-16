<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // SOC Integration Fields
            $table->unsignedBigInteger('soc_endpoint_id')->nullable()->after('id');
            $table->string('hostname')->nullable()->after('serial_number');
            $table->string('antivirus_status')->nullable()->after('mac_address');
            $table->string('usb_status')->nullable()->after('antivirus_status');
            $table->string('windows_update_status')->nullable()->after('usb_status');
            $table->string('pc_brand')->nullable()->after('manufacturer');
            $table->text('pc_specs')->nullable()->after('pc_brand');
            $table->json('installed_apps')->nullable()->after('specifications');
            $table->json('paired_hardware')->nullable()->after('installed_apps');
            $table->enum('soc_status', ['online', 'offline', 'unknown'])->default('unknown')->after('status');
            $table->timestamp('soc_last_seen')->nullable()->after('soc_status');
            $table->timestamp('soc_synced_at')->nullable()->after('soc_last_seen');
            
            $table->index('soc_endpoint_id');
            $table->index('hostname');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex(['soc_endpoint_id']);
            $table->dropIndex(['hostname']);
            
            $table->dropColumn([
                'soc_endpoint_id',
                'hostname',
                'antivirus_status',
                'usb_status',
                'windows_update_status',
                'pc_brand',
                'pc_specs',
                'installed_apps',
                'paired_hardware',
                'soc_status',
                'soc_last_seen',
                'soc_synced_at',
            ]);
        });
    }
};
