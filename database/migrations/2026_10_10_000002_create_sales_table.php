<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->decimal('total');
            $table->string('payment_method');
            $table->timestamp('sale_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
