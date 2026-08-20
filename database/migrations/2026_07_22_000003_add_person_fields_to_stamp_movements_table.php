<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stamp_movements', function (Blueprint $table) {
            $table->string('person_name', 255)->nullable()->after('description');
            $table->string('division', 255)->nullable()->after('person_name');
        });
    }

    public function down(): void
    {
        Schema::table('stamp_movements', function (Blueprint $table) {
            $table->dropColumn(['person_name', 'division']);
        });
    }
};
