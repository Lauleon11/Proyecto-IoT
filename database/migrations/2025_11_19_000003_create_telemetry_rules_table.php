<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('telemetry_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('telemetry_id')->constrained('telemetries')->onDelete('cascade');
            $table->string('operator'); // '>', '<', '='
            $table->double('threshold');
            $table->string('color'); // 'red', 'green', 'yellow'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telemetry_rules');
    }
};