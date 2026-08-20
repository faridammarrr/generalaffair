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
        Schema::table('work_activities', function (Blueprint $table) {
            $table->string('ticket_number')->nullable()->after('id');
            $table->string('priority')->default('Medium')->after('category');
            $table->string('requester')->nullable()->after('description');
            $table->string('assignee')->nullable()->after('requester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_activities', function (Blueprint $table) {
            $table->dropColumn(['ticket_number', 'priority', 'requester', 'assignee']);
        });
    }
};
