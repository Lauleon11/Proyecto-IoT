<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('device_id')->unique();
            $table->string('scope_id')->unique();
            $table->string('key')->unique();
            $table->foreignId('template_id')->constrained('templates')->onDelete('cascade');
            $table->string('device_type'); // 'real', 'digital_twin', 'python'
            $table->boolean('is_on')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};