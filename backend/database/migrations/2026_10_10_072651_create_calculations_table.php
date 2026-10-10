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
        Schema::create('calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('input_mode', 20);
            $table->foreignId('scheme_id')->constrained('layup_schemes')->restrictOnDelete();
            $table->foreignId('process_id')->constrained()->restrictOnDelete();
            $table->jsonb('quick_params');
            $table->double('effective_area_mm2');
            $table->jsonb('results');
            $table->double('mass_total_g');
            $table->bigInteger('total_cost_minor');
            $table->string('currency', 3);
            $table->jsonb('prices_snapshot');
            $table->decimal('temperature_c', 5, 2)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calculations');
    }
};
