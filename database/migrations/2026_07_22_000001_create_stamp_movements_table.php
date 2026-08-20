<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stamp_movements', function (Blueprint $table) {
            $table->id();
            $table->string('description', 500);
            $table->unsignedInteger('quantity');
            $table->string('direction', 10);
            $table->date('moved_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stamp_movements');
    }
};
