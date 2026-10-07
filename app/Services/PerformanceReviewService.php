<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Recommendation;

/**
 * AI Performance Review — deterministic rules over stored metrics.
 * Output: What worked? What did not? Next action. The review never says
 * "good job" when the bottleneck persists; it moves the loop forward.
 */
class PerformanceReviewService
{
    public function __construct(
        private KpiService $kpi,
        private FinancialAnalyticsService $finance,
        private InventoryAnalyticsService $inventory,
        private GrowthScoreService $scores,
        private RecommendationService $recommendations,
        private NotificationService $notifications,
    ) {}

    public function analyse(Business $business): ?array
    {
        $latest = $this->kpi->latest($business);
        $previous = $this->kpi->previous($business);
        if (! $latest) {
            return null;
        }

        $worked = [];
        $didnt = [];
        $next = null;
        $signals = [];

        $pct = fn ($a, $b) => $b > 0 ? ($a - $b) / $b * 100 : null;

        if ($previous) {
            $reach = $pct($latest->campaign_reach, $previous->campaign_reach);
            $convPp = $latest->conversion_rate - $previous->conversion_rate;
            $newPct = $pct($latest->new_customers, $previous->new_customers);
            $repPp = $latest->repeat_rate - $previous->repeat_rate;
            $revPct = $pct($latest->revenue, $previous->revenue);

            if ($reach !== null && $reach > 15) {
                $worked[] = 'Jangkauan kampanye naik '.pct($reach, 0).' ('.num($previous->campaign_reach).' → '.num($latest->campaign_reach).').';
            } elseif ($previous->campaign_reach === 0 && $latest->campaign_reach > 0) {
                $worked[] = 'Kampanye pertama menjangkau '.num($latest->campaign_reach).' calon pelanggan.';
            }
            if ($newPct !== null && $newPct > 5) {
                $worked[] = 'Pelanggan baru naik '.pct($newPct).' ('.num($previous->new_customers).' → '.num($latest->new_customers).').';
            }
            if ($convPp >= 0.5) {
                $worked[] = 'Conversion naik '.pct($previous->conversion_rate).' → '.pct($latest->conversion_rate).' ('.signed($convPp, 1, 'pp').').';
            }
            if ($repPp >= 1) {
                $worked[] = 'Repeat customer naik '.signed($repPp, 1, 'pp').'.';
            }
            if ($revPct !== null && $revPct > 3) {
                $worked[] = 'Omzet naik '.pct($revPct).' ('.rupiah_short($previous->revenue).' → '.rupiah_short($latest->revenue).').';
            }

            // Rule 1: reach up, conversion flat → optimise offer.
            if ($reach !== null && $reach > 15 && $convPp < 0.5) {
                $didnt[] = 'Conversion hanya bergerak '.pct($previous->conversion_rate).' → '.pct($latest->conversion_rate).' meski jangkauan naik.';
                $next ??= 'Optimalkan penawaran dan pengalaman pemesanan sebelum menambah budget iklan.';
                $signals[] = 'conversion';
            }
            // Rule 2: acquisition up, retention down → re-engagement.
            if (($newPct ?? 0) > 5 && $repPp < 0) {
                $didnt[] = 'Repeat customer turun '.pct($previous->repeat_rate).' → '.pct($latest->repeat_rate).' saat pelanggan baru bertambah.';
                $next ??= 'Bottleneck berikutnya adalah retensi: jalankan kampanye re-engagement untuk pelanggan baru agar kembali membeli.';
                $signals[] = 'retention';
            }
            // Rule 3: revenue up, margin down → cost drivers.
            if (($revPct ?? 0) > 0 && $previous->gross_margin !== null && $latest->gross_margin !== null && $latest->gross_margin < $previous->gross_margin - 1) {
                $didnt[] = 'Omzet naik, tetapi gross margin turun '.pct($previous->gross_margin).' → '.pct($latest->gross_margin).'.';
                $next ??= 'Periksa cost driver utama (bahan baku) dan sesuaikan harga produk dengan margin terendah.';
                $signals[] = 'financial_health';
            }
            if ($revPct !== null && $revPct < -3) {
                $didnt[] = 'Omzet turun '.pct(abs($revPct)).' dibanding bulan lalu.';
            }
        }

        // Rules 4–5: inventory.
        foreach ($this->inventory->insights($business) as $insight) {
            if (in_array($insight['type'], ['stockout', 'stockout_risk'], true)) {
                $didnt[] = $insight['text'];
                $next ??= 'Restock produk fast-moving sekarang: '.$insight['action'].'.';
                $signals[] = 'inventory_health';
                break;
            }
        }
        $slow = collect($this->inventory->insights($business))->firstWhere('type', 'slow');
        if ($slow) {
            $didnt[] = $slow['text'];
            $next ??= 'Buat promo bundling untuk produk slow-moving dan kurangi pembelian berikutnya.';
        }

        $score = $business->latestScore;
        if (! $next && $score) {
            $next = $this->recommendations->isExpansionMode($score)
                ? 'Semua indikator kuat. Jalankan Expansion Readiness dan eksplorasi pasar baru dengan MSIGHT / SiteSense.'
                : 'Lanjutkan fokus pada '.config("tumbuh.dimensions.{$score->bottleneck}.label").' — bottleneck terendah saat ini.';
        }

        if ($worked === []) {
            $worked[] = 'Data bulan ini sudah tercatat — fondasi untuk mengukur dampak berikutnya.';
        }
        if ($didnt === []) {
            $didnt[] = 'Tidak ada penurunan signifikan terdeteksi.';
        }

        return [
            'summary' => $this->summary($worked, $didnt, $signals),
            'worked' => $worked,
            'didnt' => $didnt,
            'next' => $next,
            'signals' => array_values(array_unique($signals)),
            'period' => $latest->period,
            'compared_to' => $previous?->period,
        ];
    }

    private function summary(array $worked, array $didnt, array $signals): string
    {
        if (in_array('conversion', $signals, true)) {
            return 'Kampanye Anda berhasil menjangkau audiens yang tepat, tetapi konversi masih di bawah target.';
        }
        if (in_array('retention', $signals, true)) {
            return 'Akuisisi Anda membaik. Bottleneck berikutnya adalah retensi pelanggan.';
        }
        if (in_array('financial_health', $signals, true)) {
            return 'Omzet bertumbuh, tetapi margin tergerus biaya.';
        }
        if (in_array('inventory_health', $signals, true)) {
            return 'Penjualan berjalan, tetapi stok berisiko menghambat pertumbuhan.';
        }

        return count($worked) > 1 ? 'Aksi Anda bulan ini memberi dampak positif yang terukur.' : 'Belum cukup perubahan untuk disimpulkan — lanjutkan misi minggu ini.';
    }

    /** Run review: recalc score, persist review, refresh next recommendation. */
    public function run(Business $business, string $trigger = 'review'): ?Recommendation
    {
        $review = $this->analyse($business);
        if (! $review) {
            return null;
        }

        $score = $this->scores->recalculate($business, $trigger) ?? $business->latestScore;
        if (! $score) {
            return null;
        }

        $business->recommendations()->where('kind', 'review')->update(['is_current' => false]);
        $rec = $this->recommendations->generate($business->fresh(), $score);
        $business->recommendations()->create([
            'growth_score_snapshot_id' => $score->id,
            'kind' => 'review',
            'bottleneck' => $rec->bottleneck,
            'headline' => $review['summary'],
            'whats_wrong' => implode(' ', $review['didnt']),
            'why_it_matters' => $rec->why_it_matters,
            'what_to_do' => $review['next'],
            'success_metric' => $rec->success_metric,
            'evidence' => $rec->evidence,
            'solution_keys' => $rec->solution_keys,
            'review' => $review,
            'is_current' => true,
        ]);

        $this->notifications->push($business, 'ai', 'AI Performance Review siap',
            $review['summary'], 'Lihat review', route('growth.review'));

        return $rec;
    }
}
