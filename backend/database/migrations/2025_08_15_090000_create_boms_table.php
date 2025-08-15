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
        Schema::create('boms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->comment('Finished good')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['product_id','name']);
        });

        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('boms')->cascadeOnDelete();
            $table->foreignId('component_product_id')->comment('Raw material')->constrained('products')->cascadeOnDelete();
            $table->decimal('quantity_per_unit', 12, 4)->default(0);
            $table->decimal('waste_factor', 5, 4)->default(0); // e.g., 0.05 = 5%
            $table->timestamps();
            $table->unique(['bom_id','component_product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_items');
        Schema::dropIfExists('boms');
    }
};