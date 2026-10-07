<?php

namespace App\Services;

use App\Models\Business;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Inventory writes with finance linkage:
 *   SALE    → stock out → income entry (revenue) with embedded COGS
 *   RESTOCK → stock in  → expense entry (cash-out, category inventory)
 * Accepts manual input today and future integrations (source column).
 */
class InventoryService
{
    public function __construct(private NotificationService $notifications) {}

    public function createProduct(Business $business, array $data): Product
    {
        return DB::transaction(function () use ($business, $data) {
            $initial = (int) ($data['stock'] ?? 0);
            $product = $business->products()->create([...$data, 'stock' => 0]);

            if ($initial > 0) {
                $this->move($product, 'initial', $initial, now()->toDateString(), 'Stok awal', 'manual');
            }

            return $product->fresh();
        });
    }

    public function restock(Product $product, int $qty, ?int $unitCost = null, ?string $date = null, string $source = 'manual', bool $recordExpense = true): InventoryMovement
    {
        return DB::transaction(function () use ($product, $qty, $unitCost, $date, $source, $recordExpense) {
            $unitCost ??= (int) $product->purchase_price;
            $date ??= now()->toDateString();
            $entryId = null;

            if ($recordExpense) {
                $entryId = $product->business->financialEntries()->create([
                    'type' => 'expense',
                    'entry_date' => $date,
                    'category' => 'inventory',
                    'amount' => $qty * $unitCost,
                    'product_id' => $product->id,
                    'description' => 'Restock '.$product->name.' ('.$qty.' '.$product->unit.')',
                    'payment_method' => 'transfer',
                    'source' => $source === 'manual' ? 'inventory' : $source,
                ])->id;
            }

            return $this->move($product, 'purchase', $qty, $date, 'Restock dari '.($product->supplier ?: 'pemasok'), $source, $unitCost, 0, $entryId);
        });
    }

    public function recordSale(Product $product, int $qty, ?string $date = null, string $paymentMethod = 'qris', string $source = 'manual', ?string $customer = null): InventoryMovement
    {
        if ($qty > $product->stock) {
            throw ValidationException::withMessages(['quantity' => 'Stok '.$product->name.' hanya '.$product->stock.' '.$product->unit.'.']);
        }

        return DB::transaction(function () use ($product, $qty, $date, $paymentMethod, $source, $customer) {
            $date ??= now()->toDateString();
            $entry = $product->business->financialEntries()->create([
                'type' => 'income',
                'entry_date' => $date,
                'category' => 'product_sales',
                'amount' => $qty * $product->selling_price,
                'cogs' => $qty * $product->purchase_price,
                'product_id' => $product->id,
                'customer' => $customer,
                'description' => 'Penjualan '.$product->name.' × '.$qty,
                'payment_method' => $paymentMethod,
                'source' => $source === 'manual' ? 'inventory' : $source,
            ]);

            $movement = $this->move($product, 'sale', -$qty, $date, 'Penjualan', $source, (int) $product->purchase_price, (int) $product->selling_price, $entry->id);

            $product->refresh();
            if ($product->stockStatus() !== 'ok' && $source === 'manual') {
                $this->notifications->push($product->business, 'warning', 'Stok '.$product->name.' menipis',
                    'Sisa '.$product->stock.' '.$product->unit.' (minimum '.$product->min_stock.').', 'Restock', route('inventory.show', $product));
            }

            return $movement;
        });
    }

    public function adjust(Product $product, int $newStock, string $note): InventoryMovement
    {
        return $this->move($product, 'adjustment', $newStock - $product->stock, now()->toDateString(), $note ?: 'Penyesuaian stok', 'manual');
    }

    private function move(Product $product, string $type, int $qty, string $date, string $note, string $source, int $unitCost = 0, int $unitPrice = 0, ?int $entryId = null): InventoryMovement
    {
        $movement = $product->movements()->create([
            'business_id' => $product->business_id,
            'type' => $type,
            'quantity' => $qty,
            'unit_cost' => $unitCost ?: $product->purchase_price,
            'unit_price' => $unitPrice,
            'moved_on' => $date,
            'financial_entry_id' => $entryId,
            'source' => $source,
            'note' => $note,
        ]);
        $product->increment('stock', $qty);

        return $movement;
    }
}
