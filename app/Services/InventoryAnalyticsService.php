<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Growth Inventory intelligence — "is inventory helping or blocking growth?"
 * Velocity is measured from sale movements over a rolling window; all
 * predictions are simple, explainable arithmetic.
 */
class InventoryAnalyticsService
{
    public const WINDOW_DAYS = 14;

    public function stats(Business $business): array
    {
        $products = $business->products()->get();

        return [
            'total' => $products->count(),
            'low' => $products->filter(fn (Product $p) => $p->stockStatus() === 'low')->count(),
            'out' => $products->filter(fn (Product $p) => $p->stockStatus() === 'out')->count(),
            'value' => (int) $products->sum(fn (Product $p) => $p->stockValue()),
            'retail_value' => (int) $products->sum(fn (Product $p) => max(0, $p->stock) * $p->selling_price),
        ];
    }

    /** Units sold per product within the window ending at the latest movement. */
    public function soldUnits(Business $business, int $days = self::WINDOW_DAYS): Collection
    {
        $anchor = $business->inventoryMovements()->max('moved_on') ?? now()->toDateString();
        $from = \Illuminate\Support\Carbon::parse($anchor)->subDays($days - 1)->toDateString();

        return $business->inventoryMovements()
            ->where('type', 'sale')
            ->whereBetween('moved_on', [$from, $anchor])
            ->selectRaw('product_id, SUM(-quantity) as units')
            ->groupBy('product_id')
            ->pluck('units', 'product_id')
            ->map(fn ($u) => (int) $u);
    }

    /** Per-product analytics rows. */
    public function products(Business $business): Collection
    {
        $sold = $this->soldUnits($business);
        $sold30 = $this->soldUnits($business, 30);
        $avgVelocity = $sold->isNotEmpty() ? $sold->sum() / max(1, $business->products()->count()) / self::WINDOW_DAYS : 0;

        return $business->products()->orderBy('name')->get()->map(function (Product $p) use ($sold, $sold30, $avgVelocity) {
            $units = $sold[$p->id] ?? 0;
            $velocity = $units / self::WINDOW_DAYS; // units/day
            $days = $velocity > 0 ? $p->stock / $velocity : null;
            $slow = $p->stock > $p->min_stock && ($sold30[$p->id] ?? 0) < max(2, $p->stock * 0.5);

            return [
                'product' => $p,
                'sold' => $units,
                'sold_30' => $sold30[$p->id] ?? 0,
                'velocity' => round($velocity, 2),
                'days_to_stockout' => $days !== null ? round($days, 1) : null,
                'speed_vs_avg' => $avgVelocity > 0 ? round(($velocity - $avgVelocity) / $avgVelocity * 100) : null,
                'movement' => $velocity >= max(0.01, $avgVelocity * 1.2) ? 'fast' : ($slow ? 'slow' : 'normal'),
                'restock_qty' => (int) max(0, ceil($velocity * 7 + $p->min_stock - $p->stock)),
                'forecast_7d' => (int) round($velocity * 7),
                'gross_profit' => $units * $p->margin(),
            ];
        });
    }

    public function topSelling(Business $business, int $limit = 3): Collection
    {
        return $this->products($business)->filter(fn ($r) => $r['sold'] > 0)->sortByDesc('sold')->take($limit)->values();
    }

    /**
     * Deterministic inventory insights (brief §29), ordered by urgency.
     *
     * @return array<int, array{type: string, tone: string, text: string, action: string, product_id: int|null}>
     */
    public function insights(Business $business): array
    {
        $rows = $this->products($business);
        if ($rows->isEmpty()) {
            return [];
        }

        $out = [];

        foreach ($rows->where('product.stock', '<=', 0) as $r) {
            $out[] = ['type' => 'stockout', 'tone' => 'bad', 'product_id' => $r['product']->id,
                'text' => $r['product']->name.' habis. Penjualan berpotensi hilang.',
                'action' => 'Restock '.max($r['restock_qty'], $r['product']->min_stock).' '.$r['product']->unit];
        }

        foreach ($rows->filter(fn ($r) => $r['days_to_stockout'] !== null && $r['product']->stock > 0 && $r['days_to_stockout'] <= 5)->sortBy('days_to_stockout') as $r) {
            $out[] = ['type' => 'stockout_risk', 'tone' => 'bad', 'product_id' => $r['product']->id,
                'text' => 'Anda bisa kehabisan '.$r['product']->name.' dalam '.num(max(1, floor($r['days_to_stockout']))).' hari berdasarkan penjualan terakhir.',
                'action' => 'Restock '.$r['restock_qty'].' '.$r['product']->unit];
        }

        $fast = $rows->where('movement', 'fast')->sortByDesc('speed_vs_avg')->first();
        if ($fast && $fast['speed_vs_avg'] > 0) {
            $out[] = ['type' => 'fast', 'tone' => 'good', 'product_id' => $fast['product']->id,
                'text' => $fast['product']->name.' terjual '.$fast['speed_vs_avg'].'% lebih cepat dari rata-rata produk lain.',
                'action' => 'Jaga stok minimum & jadikan produk utama kampanye'];
        }

        $slow = $rows->where('movement', 'slow');
        if ($slow->isNotEmpty()) {
            $out[] = ['type' => 'slow', 'tone' => 'warn', 'product_id' => $slow->first()['product']->id,
                'text' => num($slow->count()).' produk slow-moving menahan kas '.rupiah_short($slow->sum(fn ($r) => $r['product']->stockValue())).' (mis. '.$slow->first()['product']->name.').',
                'action' => 'Kurangi pembelian atau buat promo bundling'];
        }

        $lowMargin = $rows->filter(fn ($r) => $r['product']->marginPercent() < 30)->sortBy(fn ($r) => $r['product']->marginPercent())->first();
        if ($lowMargin) {
            $out[] = ['type' => 'margin', 'tone' => 'warn', 'product_id' => $lowMargin['product']->id,
                'text' => 'Margin '.$lowMargin['product']->name.' hanya '.pct($lowMargin['product']->marginPercent(), 0).'.',
                'action' => 'Tinjau harga jual atau biaya bahan'];
        }

        return $out;
    }
}
