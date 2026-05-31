<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('tripay_reference')->nullable()->after('customer_id');
            $table->string('tripay_status')->nullable()->after('tripay_reference');
            $table->string('tripay_payment_method')->nullable()->after('tripay_status');
            $table->decimal('tripay_amount_received', 12, 2)->nullable()->after('tripay_payment_method');
            $table->decimal('tripay_fee', 12, 2)->nullable()->after('tripay_amount_received');
            $table->timestamp('tripay_paid_at')->nullable()->after('tripay_fee');
            $table->timestamp('tripay_expired_at')->nullable()->after('tripay_paid_at');
            $table->json('tripay_raw_response')->nullable()->after('tripay_expired_at');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'tripay_reference',
                'tripay_status',
                'tripay_payment_method',
                'tripay_amount_received',
                'tripay_fee',
                'tripay_paid_at',
                'tripay_expired_at',
                'tripay_raw_response',
            ]);
        });
    }
};
