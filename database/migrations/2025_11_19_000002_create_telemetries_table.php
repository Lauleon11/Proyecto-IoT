<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('telemetries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('templates')->onDelete('cascade');
            $table->string('name');
            $table->string('data_type'); // 'number', 'float', 'boolean'
            $table->unsignedInteger('decimals')->nullable(); // only for float
            $table->double('min')->nullable();
            $table->double('max')->nullable();
            $table->string('unit')->nullable();
            $table->text('description')->nullable();
            $table->string('false_label')->nullable();
            $table->string('true_label')->nullable();
            $table->boolean('show_last_value')->default(true);
            $table->boolean('show_line_chart')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telemetries');
    }
};