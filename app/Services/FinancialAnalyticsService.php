<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Carbon;

/**
 * Growth Finance — server-side P&L, cash flow, margin and cost-driver
 * analytics computed from financial_entries.
 *
 *  Revenue        = Σ income
 *  COGS           = Σ embedded COGS on inventory-linked sales + Σ raw-material expenses
 *  Gross Profit   = Revenue − COGS
 *  Operating Exp. = Σ expenses excluding COGS categories and stock purchases
 *  Net Profit     = Gross Profit − Operating Expense
 *  Cash In / Out  = Σ income / Σ all expenses (stock purchases are cash-out)
 */
class FinancialAnalyticsService
{
    public function month(Business $business, Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $entries = $business->financialEntries()->whereBetween('entry_date', [$start->toDateString(), $end->toDateString()])->get();

        $income = $entries->where('type', 'income');
        $expense = $entries->where('type', 'expense');
        $cogsCats = config('tumbuh.cogs_categories');
        $nonPnl = config('tumbuh.non_pnl_expense_categories');

        $revenue = (int) $income->sum('amount');
        $cogs = (int) $income->sum('cogs') + (int) $expense->whereIn('category', $cogsCats)->sum('amount');
        $opex = (int) $expense->whereNotIn('category', array_merge($cogsCats, $nonPnl))->sum('amount');
        $gross = $revenue - $cogs;
        $net = $gross - $opex;
        $cashIn = $revenue;
        $cashOut = (int) $expense->sum('amount');

        return [
            'month' => $start,
            'label' => $start->translatedFormat('F Y'),
            'revenue' => $revenue,
            'cogs' => $cogs,
            'gross_profit' => $gross,
            'gross_margin' => $revenue > 0 ? round($gross / $revenue * 100, 1) : null,
            'opex' => $opex,
            'expenses' => $cogs + $opex,
            'net_profit' => $net,
            'net_margin' => $revenue > 0 ? round($net / $revenue * 100, 1) : null,
            'cash_in' => $cashIn,
            'cash_out' => $cashOut,
            'net_cash' => $cashIn - $cashOut,
            'entries' => $entries->count(),
            'by_category' => $expense->groupBy('category')->map(fn ($g) => (int) $g->sum('amount'))->sortDesc()->all(),
        ];
    }

    /**
     * Reporting month: the latest month with data, except early in a month
     * (before the 20th) when the previous complete month is reported instead.
     */
    public function latestMonth(Business $business): Carbon
    {
        $last = $business->financialEntries()->max('entry_date');
        if (! $last) {
            return now()->startOfMonth();
        }

        $month = Carbon::parse($last)->startOfMonth();
        $previous = $month->copy()->subMonthNoOverflow();
        $hasPrevious = $business->financialEntries()->whereBetween('entry_date', [$previous->toDateString(), $previous->copy()->endOfMonth()->toDateString()])->exists();

        if ($month->isSameMonth(now()) && now()->day < 20 && $hasPrevious) {
            return $previous;
        }

        return $month;
    }

    /** Month-to-date figures for the running month (shown alongside the report). */
    public function monthToDate(Business $business): array
    {
        return $this->month($business, now()->startOfMonth());
    }

    public function series(Business $business, int $months = 6): array
    {
        $end = $this->latestMonth($business);

        return collect(range($months - 1, 0))
            ->map(fn ($i) => $this->month($business, $end->copy()->subMonthsNoOverflow($i)))
            ->filter(fn ($m) => $m['entries'] > 0)
            ->values()->all();
    }

    public function hasData(Business $business): bool
    {
        return $business->financialEntries()->exists();
    }

    public function change(?float $now, ?float $before): ?float
    {
        if ($now === null || $before === null || $before == 0) {
            return null;
        }

        return round(($now - $before) / abs($before) * 100, 1);
    }

    /** Dashboard summary with month-over-month change. */
    public function summary(Business $business): array
    {
        $month = $this->latestMonth($business);
        $cur = $this->month($business, $month);
        $prev = $this->month($business, $month->copy()->subMonthNoOverflow());

        return [
            'current' => $cur,
            'previous' => $prev,
            'revenue_change' => $this->change($cur['revenue'], $prev['revenue']),
            'expense_change' => $this->change($cur['expenses'], $prev['expenses']),
            'profit_change' => $this->change($cur['net_profit'], $prev['net_profit']),
        ];
    }

    /**
     * Top cost drivers: COGS (bahan baku / harga pokok) plus the largest
     * operating expense categories, with month-over-month change.
     */
    public function costDrivers(Business $business): array
    {
        $month = $this->latestMonth($business);
        $cur = $this->month($business, $month);
        $prev = $this->month($business, $month->copy()->subMonthNoOverflow());

        $drivers = collect($cur['by_category'])
            ->except(array_merge(config('tumbuh.non_pnl_expense_categories'), config('tumbuh.cogs_categories')))
            ->map(fn ($amount, $cat) => [
                'category' => $cat,
                'label' => config("tumbuh.expense_categories.{$cat}", $cat),
                'amount' => $amount,
                'change' => $this->change($amount, $prev['by_category'][$cat] ?? null),
            ]);

        if ($cur['cogs'] > 0) {
            $drivers->prepend([
                'category' => 'cogs',
                'label' => 'Bahan baku / COGS',
                'amount' => $cur['cogs'],
                'change' => $this->change($cur['cogs'], $prev['cogs'] ?: null),
            ], 'cogs');
        }

        return $drivers->sortByDesc('amount')->take(3)->values()->all();
    }

    /** Gold: 0–100 financial health from margin, cash, cost growth and record discipline. */
    public function healthScore(Business $business): ?int
    {
        $s = $this->summary($business);
        $cur = $s['current'];
        if ($cur['revenue'] <= 0) {
            return null;
        }

        $margin = min(100, max(0, ($cur['gross_margin'] ?? 0) / 50 * 100));
        $cash = $cur['net_cash'] >= 0 ? 100 : max(0, 100 + $cur['net_cash'] / max(1, $cur['revenue']) * 200);
        $costGrowth = $s['expense_change'] !== null && $s['revenue_change'] !== null
            ? max(0, min(100, 70 + ($s['revenue_change'] - $s['expense_change']) * 3))
            : 60;
        $discipline = min(100, $cur['entries'] / 30 * 100);

        return (int) round(0.35 * $margin + 0.25 * $cash + 0.25 * $costGrowth + 0.15 * $discipline);
    }

    /** Deterministic AI financial insight (brief §20). */
    public function insight(Business $business): array
    {
        $s = $this->summary($business);
        $cur = $s['current'];
        $prev = $s['previous'];

        if ($cur['revenue'] <= 0) {
            return [
                'headline' => 'Belum ada transaksi tercatat bulan ini.',
                'action' => 'Mulai catat pemasukan dan pengeluaran di Growth Finance agar laba rugi dapat dihitung.',
                'target_margin' => 40,
                'evidence' => [['label' => 'Transaksi tercatat', 'value' => '0', 'tone' => 'bad']],
                'tone' => 'neutral',
            ];
        }

        $drivers = $this->costDrivers($business);
        $top = $drivers[0] ?? null;
        $marginNow = $cur['gross_margin'];
        $marginBefore = $prev['gross_margin'];
        $revChange = $s['revenue_change'];
        $rawChange = $this->change($cur['cogs'], $prev['cogs'] ?: null);

        if ($marginBefore !== null && $marginNow !== null && $marginNow < $marginBefore - 1) {
            $headline = ($revChange !== null && $revChange > 0 ? 'Omzet naik '.pct($revChange).', tetapi ' : '')
                .($rawChange !== null && $rawChange > 0 ? 'biaya bahan baku (COGS) naik '.pct($rawChange).'. ' : '')
                .'Gross margin turun dari '.pct($marginBefore).' → '.pct($marginNow).'.';
            $action = 'Review 3 cost driver teratas'.($top ? ' (terbesar: '.$top['label'].' '.rupiah_short($top['amount']).')' : '').', bandingkan harga pemasok, dan sesuaikan harga produk dengan margin terendah.';
            $tone = 'bad';
        } elseif ($revChange !== null && $revChange > 0) {
            $headline = 'Omzet naik '.pct($revChange).' dan margin terjaga di '.pct($marginNow).'. Laba bersih '.rupiah_short($cur['net_profit']).'.';
            $action = 'Pertahankan struktur biaya. Alokasikan sebagian laba untuk kampanye yang terbukti menghasilkan.';
            $tone = 'good';
        } else {
            $headline = 'Gross margin '.pct($marginNow).', laba bersih '.rupiah_short($cur['net_profit']).' bulan ini.';
            $action = 'Pantau cost driver utama'.($top ? ' ('.$top['label'].')' : '').' dan pastikan semua pengeluaran tercatat.';
            $tone = 'neutral';
        }

        return [
            'headline' => ucfirst($headline),
            'action' => $action,
            'target_margin' => max(40, (int) ceil($marginBefore ?? 40)),
            'tone' => $tone,
            'evidence' => array_values(array_filter([
                ['label' => 'Gross margin', 'value' => $marginNow !== null ? pct($marginNow) : '—', 'tone' => $tone === 'bad' ? 'bad' : 'good'],
                $marginBefore !== null ? ['label' => 'Bulan lalu', 'value' => pct($marginBefore), 'tone' => 'neutral'] : null,
                $top ? ['label' => 'Cost driver #1', 'value' => $top['label'], 'tone' => 'neutral'] : null,
            ])),
        ];
    }
}
