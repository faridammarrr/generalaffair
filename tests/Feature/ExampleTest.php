<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\PettyCash;
use App\Models\RequestItem;
use App\Models\StampMovement;
use App\Models\WorkActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Operasional');
    }

    public function test_request_item_form_lists_transfer_cc_bni_payment_method(): void
    {
        $response = $this->get(route('request-items.create'));

        $response->assertStatus(200);
        $response->assertSee('Transfer CC BNI');
    }

    public function test_a_request_item_can_be_created(): void
    {
        $response = $this->post(route('request-items.store'), [
            'name' => 'Pembelian ATK',
            'request_date' => '2026-07-22',
            'letter_number' => 'GA/PR/VII/001',
            'requestor_name' => 'Farid Ammar',
            'division' => 'General Affair',
            'budget_id' => 'GA-2026-001',
            'budget_name' => 'Office Supplies',
            'receiver_name' => 'PT Vendor Utama',
            'payment_due_date' => '2026-07-30',
            'payment_method' => 'Bank Transfer',
            'bank_account_number' => '1234567890',
            'amount' => 250000,
            'type' => 'Petty Cash',
            'status' => 'Draft',
        ]);

        $response->assertRedirect(route('request-items.index'));

        $this->assertDatabaseHas('request_items', [
            'name' => 'Pembelian ATK',
            'letter_number' => 'GA/PR/VII/001',
            'requestor_name' => 'Farid Ammar',
            'division' => 'General Affair',
            'budget_id' => 'GA-2026-001',
            'budget_name' => 'Office Supplies',
            'receiver_name' => 'PT Vendor Utama',
            'payment_method' => 'Bank Transfer',
            'bank_account_number' => '1234567890',
            'amount' => 250000,
            'type' => 'Petty Cash',
            'status' => 'Draft',
        ]);
    }

    public function test_request_item_status_can_move_to_paid(): void
    {
        $item = RequestItem::create([
            'name' => 'Transport vendor',
            'amount' => 500000,
            'type' => 'Direct Payment',
            'status' => 'Draft',
        ]);

        $this->patch(route('request-items.submit', $item))->assertRedirect(route('request-items.index'));
        $this->assertSame('Submitted', $item->fresh()->status);
        $this->assertNotNull($item->fresh()->submitted_at);

        $this->patch(route('request-items.paid', $item))->assertRedirect(route('request-items.index'));
        $this->assertSame('Paid', $item->fresh()->status);
        $this->assertNotNull($item->fresh()->paid_at);
    }

    public function test_stamp_stock_is_tracked_from_movements(): void
    {
        StampMovement::create([
            'description' => 'Stok awal',
            'quantity' => 20,
            'direction' => 'in',
            'moved_at' => '2026-07-01',
        ]);

        $this->post(route('stamp-movements.store'), [
            'description' => 'Dokumen vendor',
            'person_name' => 'Budi Santoso',
            'division' => 'Finance',
            'quantity' => 3,
            'direction' => 'out',
            'moved_at' => '2026-07-22',
        ])->assertRedirect(route('stamp-movements.index'));

        $response = $this->get(route('stamp-movements.index'));

        $response->assertStatus(200);
        $response->assertSee('Sisa Meterai');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Finance');
        $response->assertSee('17');
        $response->assertSee('Pinjam');
    }

    public function test_borrowed_stamps_still_reduce_remaining_stock(): void
    {
        StampMovement::create([
            'description' => 'Stok awal',
            'quantity' => 20,
            'direction' => 'in',
            'moved_at' => '2026-07-01',
        ]);

        $movement = StampMovement::create([
            'description' => 'Pinjam ke divisi umum',
            'person_name' => 'Dewi Lestari',
            'division' => 'Umum',
            'quantity' => 3,
            'direction' => 'borrow',
            'moved_at' => '2026-07-22',
        ]);

        $this->assertSame(-3, $movement->signed_quantity);

        $response = $this->get(route('stamp-movements.index'));

        $response->assertStatus(200);
        $response->assertSee('Pinjam');
        $response->assertSee('Dewi Lestari');
        $response->assertSee('17');
    }

    public function test_borrowed_stamp_can_be_returned_and_restocked(): void
    {
        StampMovement::create([
            'description' => 'Stok awal',
            'quantity' => 20,
            'direction' => 'in',
            'moved_at' => '2026-07-01',
        ]);

        $movement = StampMovement::create([
            'description' => 'Pinjam ke divisi umum',
            'person_name' => 'Dewi Lestari',
            'division' => 'Umum',
            'quantity' => 3,
            'direction' => 'borrow',
            'moved_at' => '2026-07-22',
        ]);

        $this->post(route('stamp-movements.return', $movement))
            ->assertRedirect(route('stamp-movements.index'));

        $movement->refresh();

        $this->assertSame('Pengembalian meterai: Pinjam ke divisi umum', $movement->description);
        $this->assertSame('in', $movement->direction);
        $this->assertSame(23, StampMovement::all()->sum(fn ($item) => $item->signed_quantity));
        $this->assertSame(2, StampMovement::count());
    }

    public function test_activity_tracking_page_can_be_opened(): void
    {
        $response = $this->get(route('activities.index'));

        $response->assertStatus(200);
        $response->assertSee('Tracking Kegiatan GA');
        $response->assertSee('Tambah Kegiatan');
    }

    public function test_activity_can_be_created_from_the_tracking_page(): void
    {
        $response = $this->post(route('activities.store'), [
            'date' => '2026-07-23',
            'category' => 'GA',
            'title' => 'Cek kebersihan kantor',
            'description' => 'Area kantor bersih dan aman',
            'status' => 'Selesai',
        ]);

        $response->assertRedirect(route('activities.index'));

        $this->assertDatabaseHas('work_activities', [
            'category' => 'GA',
            'title' => 'Cek kebersihan kantor',
            'status' => 'Selesai',
        ]);
    }

    public function test_activity_can_be_updated(): void
    {
        $activity = WorkActivity::create([
            'date' => '2026-07-23',
            'category' => 'GA',
            'title' => 'Cek kebersihan kantor',
            'description' => 'Awal',
            'status' => 'Proses',
        ]);

        $response = $this->put(route('activities.update', $activity), [
            'date' => '2026-07-24',
            'category' => 'IT Support',
            'title' => 'Perbaiki printer',
            'description' => 'Printer sudah diperbaiki',
            'status' => 'Selesai',
        ]);

        $response->assertRedirect(route('activities.index'));

        $this->assertDatabaseHas('work_activities', [
            'id' => $activity->id,
            'category' => 'IT Support',
            'title' => 'Perbaiki printer',
            'status' => 'Selesai',
        ]);
    }

    public function test_tracking_page_groups_activities_by_date(): void
    {
        WorkActivity::create([
            'date' => '2026-07-23',
            'category' => 'GA',
            'title' => 'Cek kebersihan kantor',
            'description' => 'Area kantor bersih',
            'status' => 'Selesai',
        ]);

        WorkActivity::create([
            'date' => '2026-07-23',
            'category' => 'IT Support',
            'title' => 'Perbaiki printer',
            'description' => 'Printer sudah berjalan',
            'status' => 'Selesai',
        ]);

        $response = $this->get(route('activities.index'));

        $response->assertStatus(200);
        $response->assertSee('date-group', false);
        $response->assertSee('23 Jul 2026', false);
    }

    public function test_bills_page_displays_chart_section(): void
    {
        $response = $this->get(route('bills.index'));

        $response->assertStatus(200);
        $response->assertSee('Grafik Reminder Bills');
    }

    public function test_petty_cash_page_can_be_opened(): void
    {
        $response = $this->get(route('petty-cashes.index'));

        $response->assertStatus(200);
        $response->assertSee('Catat Belanja Petty Cash');
    }

    public function test_petty_cash_invoice_can_be_downloaded(): void
    {
        $item = PettyCash::create([
            'date' => '2026-07-22',
            'description' => 'Belanja ATK kantor',
            'amount' => 125000,
            'category' => 'ATK',
            'person_name' => 'Budi',
            'invoice_path' => 'petty-cash-invoices/test.pdf',
        ]);

        Storage::disk('public')->put($item->invoice_path, "%PDF-1.4\n%Test PDF\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF");

        $response = $this->get(route('petty-cashes.invoice', $item));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_petty_cash_can_be_created(): void
    {
        $response = $this->post(route('petty-cashes.store'), [
            'date' => '2026-07-22',
            'description' => 'Belanja ATK kantor',
            'amount' => 125000,
            'category' => 'ATK',
            'person_name' => 'Budi',
            'notes' => 'Pembelian alat tulis',
        ]);

        $response->assertRedirect(route('petty-cashes.index'));

        $this->assertDatabaseHas('petty_cashes', [
            'description' => 'Belanja ATK kantor',
            'amount' => 125000,
            'category' => 'ATK',
            'person_name' => 'Budi',
        ]);
    }

    public function test_bill_can_be_tracked_and_marked_paid(): void
    {
        $this->post(route('bills.store'), [
            'name' => 'Internet kantor Juli',
            'vendor' => 'Telkom',
            'amount' => 750000,
            'due_date' => '2026-07-25',
            'status' => 'Unpaid',
            'notes' => 'Invoice bulanan',
        ])->assertRedirect(route('bills.index'));

        $bill = Bill::first();

        $this->assertSame('Unpaid', $bill->status);
        $this->patch(route('bills.paid', $bill))->assertRedirect(route('bills.index'));

        $this->assertSame('Paid', $bill->fresh()->status);
        $this->assertNotNull($bill->fresh()->paid_at);
    }

    public function test_bill_can_store_settlement_amount_and_show_remaining_return(): void
    {
        $response = $this->post(route('bills.store'), [
            'name' => 'Settlement test',
            'vendor' => 'Vendor',
            'type' => 'Cash Advance Settlement',
            'amount' => 750000,
            'settlement_amount' => 500000,
            'due_date' => '2026-07-25',
            'status' => 'Unpaid',
            'notes' => 'Invoice bulanan',
        ]);

        $response->assertRedirect(route('bills.index'));
        $this->assertDatabaseHas('bills', [
            'name' => 'Settlement test',
            'settlement_amount' => 500000,
        ]);

        $page = $this->get(route('bills.index'));
        $page->assertStatus(200);
        $page->assertSee('Sisa Kembalian');
        $page->assertSee('Rp 250.000');
    }

    public function test_stamp_movement_can_be_updated(): void
    {
        $movement = StampMovement::create([
            'description' => 'Dokumen lama',
            'person_name' => 'Ani',
            'division' => 'HR',
            'quantity' => 1,
            'direction' => 'out',
            'moved_at' => '2026-07-20',
        ]);

        $this->put(route('stamp-movements.update', $movement), [
            'description' => 'Dokumen kontrak',
            'person_name' => 'Budi Santoso',
            'division' => 'Finance',
            'quantity' => 2,
            'direction' => 'out',
            'moved_at' => '2026-07-22',
        ])->assertRedirect(route('stamp-movements.index'));

        $this->assertDatabaseHas('stamp_movements', [
            'id' => $movement->id,
            'description' => 'Dokumen kontrak',
            'person_name' => 'Budi Santoso',
            'division' => 'Finance',
            'quantity' => 2,
        ]);
    }

    public function test_reports_page_uses_filters_and_shows_live_data(): void
    {
        PettyCash::create([
            'date' => '2026-08-05',
            'description' => 'Kertas printer',
            'amount' => 150000,
            'category' => 'ATK',
        ]);

        RequestItem::create([
            'name' => 'Transport vendor',
            'request_date' => '2026-08-05',
            'amount' => 300000,
            'type' => 'Transport',
            'status' => 'Submitted',
        ]);

        $response = $this->get(route('reports.index', [
            'month' => 8,
            'year' => 2026,
            'category' => 'ATK',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Kertas printer');
        $response->assertSee('Operational Expense Report');
        $response->assertDontSee('Transport vendor');
    }

    public function test_reports_can_be_exported_as_filtered_csv(): void
    {
        PettyCash::create([
            'date' => '2026-08-05',
            'description' => 'Air minum kantor',
            'amount' => 75000,
            'category' => 'Konsumsi',
        ]);

        $response = $this->get(route('reports.export', [
            'type' => 'expense',
            'month' => 8,
            'year' => 2026,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertStreamedContent("\xEF\xBB\xBFTanggal;\"Nomor Transaksi\";Kategori;Deskripsi;Nominal;PIC;Status\n2026-08-05;PC-00001;Konsumsi;\"Air minum kantor\";75000;-;Recorded\n");
    }
}
