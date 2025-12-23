<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->string('shipping_receipt_number')->nullable()->after('signed_pdf_path');
            $table->string('shipping_receipt_path')->nullable()->after('shipping_receipt_number');
            $table->timestamp('shipping_sent_at')->nullable()->after('shipping_receipt_path');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_receipt_number',
                'shipping_receipt_path',
                'shipping_sent_at',
            ]);
        });
    }
};
