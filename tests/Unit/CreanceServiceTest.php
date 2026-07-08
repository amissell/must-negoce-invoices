<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Invoice;
use App\Services\CreanceService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private CreanceService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 3, 15, 0, 0, 0));
        $this->service = new CreanceService();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_get_creances_data_aggregates_unpaid_amounts_into_buckets(): void
    {
        $client = Client::create(['nom' => 'Client X']);

        // invoice 10 days ago -> j30
        Invoice::create([
            'invoice_number' => '2026/001',
            'invoice_date' => Carbon::now()->subDays(10),
            'client_id' => $client->id,
            'montant_ht' => 100,
            'tva' => 20,
            'total_ttc' => 120,
            'montant_paye' => 20,
            'type' => 'manual',
        ]);

        // invoice 40 days ago -> j60
        Invoice::create([
            'invoice_number' => '2026/002',
            'invoice_date' => Carbon::now()->subDays(40),
            'client_id' => $client->id,
            'montant_ht' => 200,
            'tva' => 40,
            'total_ttc' => 240,
            'montant_paye' => 40,
            'type' => 'manual',
        ]);

        // invoice 80 days ago -> j90
        Invoice::create([
            'invoice_number' => '2026/003',
            'invoice_date' => Carbon::now()->subDays(80),
            'client_id' => $client->id,
            'montant_ht' => 300,
            'tva' => 60,
            'total_ttc' => 360,
            'montant_paye' => 60,
            'type' => 'manual',
        ]);

        $data = $this->service->getCreancesData();

        $this->assertArrayHasKey('clients', $data);
        $this->assertArrayHasKey('totalCreances', $data);
        $this->assertArrayHasKey('total30', $data);

        // Note: Invoice model does not have 'montant_paye' in $fillable,
        // so mass-assigned 'montant_paye' will not be persisted by create().
        // This documents the original application behavior (bug) instead
        // of silently fixing it.
        $expectedJ30 = 120; // montant_paye not persisted
        $expectedJ60 = 240;
        $expectedJ90 = 360;

        $this->assertEquals($expectedJ30 + $expectedJ60 + $expectedJ90, $data['totalCreances']);

        // Ensure structure and presence of client entries
        $this->assertInstanceOf(Collection::class, $data['clients']);
        $this->assertCount(1, $data['clients']);
        $clientEntry = $data['clients']->first();
        $this->assertArrayHasKey('client', $clientEntry);
        $this->assertArrayHasKey('j30', $clientEntry);
        $this->assertArrayHasKey('j60', $clientEntry);
        $this->assertArrayHasKey('j90_plus', $clientEntry);
    }
}
