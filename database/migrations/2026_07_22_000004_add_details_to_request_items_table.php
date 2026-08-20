<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->date('request_date')->nullable()->after('name');
            $table->string('letter_number', 255)->nullable()->after('request_date');
            $table->string('requestor_name', 255)->nullable()->after('letter_number');
            $table->string('division', 255)->nullable()->after('requestor_name');
            $table->string('budget_id', 255)->nullable()->after('division');
            $table->string('budget_name', 255)->nullable()->after('budget_id');
            $table->string('receiver_name', 255)->nullable()->after('budget_name');
            $table->date('payment_due_date')->nullable()->after('receiver_name');
            $table->string('payment_method', 255)->nullable()->after('payment_due_date');
            $table->string('bank_account_number', 255)->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->dropColumn([
                'request_date',
                'letter_number',
                'requestor_name',
                'division',
                'budget_id',
                'budget_name',
                'receiver_name',
                'payment_due_date',
                'payment_method',
                'bank_account_number',
            ]);
        });
    }
};
