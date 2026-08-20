<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FuelUsageTest extends TestCase
{
    use RefreshDatabase;

    public function test_fuel_monitoring_page_can_be_rendered(): void
    {
        $response = $this->get('/fuel-usages');

        $response->assertOk();
        $response->assertSee('Fuel Usage');
    }

    public function test_fuel_usage_can_be_created(): void
    {
        $response = $this->post('/fuel-usages', [
            'driver_name' => 'Rudi',
            'date' => '2026-08-04',
            'amount' => 95000,
            'budget_month' => 8,
            'budget_year' => 2026,
            'notes' => 'Pembelian untuk perjalanan kantor',
        ]);

        $response->assertRedirect('/fuel-usages');
        $this->assertDatabaseHas('fuel_usages', [
            'driver_name' => 'Rudi',
            'amount' => 95000,
            'budget_month' => 8,
            'budget_year' => 2026,
            'notes' => 'Pembelian untuk perjalanan kantor',
        ]);
    }

    public function test_fuel_usage_can_be_created_with_description_field(): void
    {
        Schema::dropIfExists('fuel_usages');

        Schema::create('fuel_usages', function (Blueprint $table) {
            $table->id();
            $table->string('person');
            $table->string('driver_name')->nullable();
            $table->date('date')->nullable();
            $table->integer('amount')->default(0);
            $table->integer('budget_month')->nullable();
            $table->integer('budget_year')->nullable();
            $table->string('description');
            $table->timestamps();
        });

        $response = $this->post('/fuel-usages', [
            'person' => 'Ari',
            'driver_name' => 'Ari',
            'date' => '2026-08-14',
            'amount' => 200000,
            'budget_month' => 8,
            'budget_year' => 2026,
            'description' => 'Pembelian bensin untuk operasional kantor',
        ]);

        $response->assertRedirect('/fuel-usages');
        $this->assertDatabaseHas('fuel_usages', [
            'person' => 'Ari',
            'driver_name' => 'Ari',
            'amount' => 200000,
            'description' => 'Pembelian bensin untuk operasional kantor',
        ]);
    }

    public function test_index_works_when_budget_columns_are_missing(): void
    {
        Schema::dropIfExists('fuel_usages');

        Schema::create('fuel_usages', function (Blueprint $table) {
            $table->id();
            $table->string('driver_name')->nullable();
            $table->integer('amount')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $response = $this->get('/fuel-usages');

        $response->assertOk();
    }
}
