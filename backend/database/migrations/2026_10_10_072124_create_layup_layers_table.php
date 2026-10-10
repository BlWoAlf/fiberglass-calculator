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
        Schema::create('layup_layers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheme_id')->constrained('layup_schemes')->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->decimal('coverage', 5, 4);
            $table->decimal('overlap_waste', 5, 4);
            $table->text('note')->nullable();

            $table->unique(['scheme_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layup_layers');
    }
};
