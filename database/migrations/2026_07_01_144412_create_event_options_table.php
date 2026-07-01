<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_options', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->dateTime('date_time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_options');
    }
};
