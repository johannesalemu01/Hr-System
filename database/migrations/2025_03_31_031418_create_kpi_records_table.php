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
        Schema::create('kpi_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_kpi_id')->constrained()->onDelete('cascade');
            $table->decimal('actual_value', 12, 2);
            $table->decimal('achievement_percentage', 8, 2)->nullable();
            $table->date('record_date');
            $table->text('comments')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->integer('points_earned')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_records');
    }
};