<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use App\Services\FinancialAnalyticsService;
use App\Services\InventoryAnalyticsService;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FinanceInventoryTest extends TestCase
{
    use RefreshDatabase;

    private function business(): Business
    {
        $user = User::create(['name' => 'T', 'email' => 't@example.com', 'phone' => '081200000000', 'password' => 'rahasia123']);
        $user->forceFill(['phone_verified_at' => now()])->save();

        return $user->business()->create(['name' => 'Toko T', 'tier' => 'silver', 'onboarded_at' => now(), 'goals_set_at' => now()]);
    }

    public function test_profit_and_loss_and_cash_flow(): void
    {
        $b = $this->business();
        $d = now()->startOfMonth()->addDays(2)->toDateString();
        $b->financialEntries()->createMany([
            ['type' => 'income', 'entry_date' => $d, 'category' => 'product_sales', 'amount' => 10_000_000, 'cogs' => 4_000_000],
            ['type' => 'income', 'entry_date' => $d, 'category' => 'service', 'amount' => 2_000_000],
            ['type' => 'expense', 'entry_date' => $d, 'category' => 'raw_material', 'amount' => 1_000_000],
            ['type' => 'expense', 'entry_date' => $d, 'category' => 'salary', 'amount' => 3_000_000],
            ['type' => 'expense', 'entry_date' => $d, 'category' => 'inventory', 'amount' => 2_500_000], // stock purchase: cash-out only
        ]);

        $m = app(FinancialAnalyticsService::class)->month($b, now());
        $this->assertSame(12_000_000, $m['revenue']);
        $this->assertSame(5_000_000, $m['cogs']);           // embedded 4M + raw material 1M
        $this->assertSame(7_000_000, $m['gross_profit']);
        $this->assertSame(3_000_000, $m['opex']);           // inventory purchase excluded
        $this->assertSame(4_000_000, $m['net_profit']);
        $this->assertSame(12_000_000, $m['cash_in']);
        $this->assertSame(6_500_000, $m['cash_out']);
        $this->assertEqualsWithDelta(58.3, $m['gross_margin'], 0.05);
    }

    public function test_income_and_expense_forms_persist(): void
    {
        $b = $this->business();
        $this->actingAs($b->user);
        $this->post(route('finance.income.store'), ['entry_date' => now()->toDateString(), 'category' => 'product_sales', 'amount' => 35000, 'payment_method' => 'qris', 'description' => 'Coffee'])->assertRedirect(route('finance.index'));
        $this->post(route('finance.expense.store'), ['entry_date' => now()->addDay()->toDateString(), 'category' => 'raw_material', 'amount' => 1, 'payment_method' => 'cash', 'description' => 'x'])->assertSessionHasErrors('entry_date');
        $this->assertSame(1, $b->financialEntries()->count());
    }

    public function test_receipt_ocr_simulation_is_deterministic_and_confirms_to_ledger(): void
    {
        $b = $this->business();
        $this->actingAs($b->user);
        $this->post(route('finance.scan.upload'), ['sample' => 0])->assertRedirect();
        $receipt = $b->receipts()->first();
        $this->assertSame('raw_material', $receipt->parsed['suggested_category']);
        $this->post(route('finance.scan.confirm', $receipt), ['merchant' => $receipt->parsed['merchant'], 'entry_date' => now()->toDateString(), 'category' => 'raw_material', 'amount' => $receipt->parsed['total'], 'payment_method' => 'cash'])
            ->assertRedirect(route('finance.index'));
        $this->assertSame('receipt_ocr', $b->financialEntries()->first()->source);
        $this->assertSame('confirmed', $receipt->fresh()->status);
    }

    public function test_sale_reduces_stock_and_links_revenue_and_cogs(): void
    {
        $b = $this->business();
        $svc = app(InventoryService::class);
        $p = $svc->createProduct($b, ['name' => 'Tiramisu Cake', 'sku' => 'TIRA-001', 'purchase_price' => 18000, 'selling_price' => 35000, 'stock' => 18, 'min_stock' => 5, 'unit' => 'pcs', 'supplier' => 'ABC Supplier']);
        $this->assertSame(18, $p->stock);

        $svc->recordSale($p, 14);
        $p->refresh();
        $this->assertSame(4, $p->stock);
        $this->assertSame('low', $p->stockStatus());
        $income = $b->financialEntries()->where('type', 'income')->first();
        $this->assertSame(14 * 35000, $income->amount);
        $this->assertSame(14 * 18000, $income->cogs);

        $this->expectException(ValidationException::class);
        $svc->recordSale($p, 5); // only 4 left
    }

    public function test_restock_records_cash_out_and_inventory_value(): void
    {
        $b = $this->business();
        $svc = app(InventoryService::class);
        $p = $svc->createProduct($b, ['name' => 'Matcha', 'sku' => 'M-1', 'purchase_price' => 20000, 'selling_price' => 38000, 'stock' => 0, 'min_stock' => 6, 'unit' => 'pcs']);
        $this->assertSame('out', $p->stockStatus());

        $svc->restock($p, 10);
        $this->assertSame(10, $p->fresh()->stock);
        $this->assertSame(200000, $b->financialEntries()->where('category', 'inventory')->value('amount'));
        $this->assertSame(200000, app(InventoryAnalyticsService::class)->stats($b)['value']);
        $this->assertSame(10, $p->movements()->sum('quantity'));
    }

    public function test_inventory_insights_flag_stockout_risk(): void
    {
        $sari = $this->scenario('sari');
        $types = collect(app(InventoryAnalyticsService::class)->insights($sari))->pluck('type');
        $this->assertContains('stockout', $types);
        $this->assertContains('stockout_risk', $types);
        $this->assertContains('slow', $types);
    }
}
