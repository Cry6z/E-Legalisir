<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->unsignedInteger('legalisir_fee')->default(0)->after('file_path');
            $table->unsignedInteger('photocopy_fee')->default(0)->after('legalisir_fee');
            $table->unsignedInteger('shipping_fee')->default(0)->after('photocopy_fee');
            $table->unsignedInteger('total_fee')->default(0)->after('shipping_fee');
            $table->enum('payment_status', ['pending', 'paid'])->default('pending')->after('total_fee');
            $table->timestamp('payment_confirmed_at')->nullable()->after('payment_status');
            $table->text('payment_notes')->nullable()->after('payment_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->dropColumn([
                'legalisir_fee',
                'photocopy_fee',
                'shipping_fee',
                'total_fee',
                'payment_status',
                'payment_confirmed_at',
                'payment_notes',
            ]);
        });
    }
};
