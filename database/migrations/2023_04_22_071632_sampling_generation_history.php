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
        Schema::create('sampling_generation_history', function (Blueprint $table) {
            $table->id()->autoIncrement();
            $table->integer('target_audit');
            $table->integer('actual_outcome');
            $table->string('change_in_percent');
            $table->timestamps('');
        });

        Schema::table('sampling_generation_history', function($table) {
            $table->string('generated_by')->after('id');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sampling_generation_history', function($table) {
            $table->dropColumn('generated_by');
        });
    }
};
