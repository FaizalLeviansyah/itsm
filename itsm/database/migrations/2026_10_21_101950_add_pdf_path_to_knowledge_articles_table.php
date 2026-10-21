<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('content');
        });
    }

    public function down()
    {
        Schema::table('knowledge_articles', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
        });
    }
};
