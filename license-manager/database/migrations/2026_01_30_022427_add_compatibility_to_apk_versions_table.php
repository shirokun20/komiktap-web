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
        Schema::table('apk_versions', function (Blueprint $table) {
            $table->string('min_android_version')->nullable()->after('version_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apk_versions', function (Blueprint $table) {
            $table->dropColumn('min_android_version');
        });
    }
};
