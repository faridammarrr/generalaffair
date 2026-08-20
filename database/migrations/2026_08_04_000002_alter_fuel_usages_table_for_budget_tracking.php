<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_usages', function (Blueprint $table) {
            if (! Schema::hasColumn('fuel_usages', 'driver_name')) {
                $table->string('driver_name')->nullable()->after('id');
            }

            if (! Schema::hasColumn('fuel_usages', 'date')) {
                $table->date('date')->nullable()->after('driver_name');
            }

            if (! Schema::hasColumn('fuel_usages', 'amount')) {
                $table->integer('amount')->default(0)->after('date');
            }

            if (! Schema::hasColumn('fuel_usages', 'budget_month')) {
                $table->integer('budget_month')->nullable()->after('amount');
            }

            if (! Schema::hasColumn('fuel_usages', 'budget_year')) {
                $table->integer('budget_year')->nullable()->after('budget_month');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fuel_usages', function (Blueprint $table) {
            if (Schema::hasColumn('fuel_usages', 'driver_name')) {
                $table->dropColumn('driver_name');
            }

            if (Schema::hasColumn('fuel_usages', 'date')) {
                $table->dropColumn('date');
            }

            if (Schema::hasColumn('fuel_usages', 'amount')) {
                $table->dropColumn('amount');
            }

            if (Schema::hasColumn('fuel_usages', 'budget_month')) {
                $table->dropColumn('budget_month');
            }

            if (Schema::hasColumn('fuel_usages', 'budget_year')) {
                $table->dropColumn('budget_year');
            }
        });
    }
};
