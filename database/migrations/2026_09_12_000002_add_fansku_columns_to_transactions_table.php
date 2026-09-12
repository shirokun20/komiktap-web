<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('fansku_support_id')->nullable()->unique()->after('tripay_raw_response');
            $table->string('fansku_code')->nullable()->after('fansku_support_id');
            $table->string('fansku_status')->nullable()->after('fansku_code');
            $table->timestamp('fansku_paid_at')->nullable()->after('fansku_status');
            $table->json('fansku_raw_response')->nullable()->after('fansku_paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['fansku_support_id']);
            $table->dropColumn([
                'fansku_support_id',
                'fansku_code',
                'fansku_status',
                'fansku_paid_at',
                'fansku_raw_response',
            ]);
        });
    }
};
