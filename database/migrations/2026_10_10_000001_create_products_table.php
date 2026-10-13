<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('category');
            $table->string('unit')->default('each');
            $table->decimal('price');
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('reorder_level')->default(5);
            $table->timestamps();
            $table->index(['category', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
