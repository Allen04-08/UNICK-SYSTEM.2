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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['raw','finished'])->index();
            $table->unsignedInteger('reorder_point')->default(0);
            $table->unsignedInteger('safety_stock')->default(0);
            $table->unsignedInteger('lead_time_days')->default(0);
            $table->unsignedDecimal('unit_price', 12, 2)->default(0);
            $table->unsignedInteger('stock_on_hand')->default(0);
            $table->unsignedInteger('stock_allocated')->default(0);
            $table->timestamps();
            $table->index(['name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
