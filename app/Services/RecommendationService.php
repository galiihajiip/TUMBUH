<?php

namespace App\Services;

use App\Models\Business;
use App\Models\GrowthScoreSnapshot;
use App\Models\Recommendation;
use App\Models\TelkomselSolution;

/**
 * AI Growth Assistant engine — a deterministic, rule-based insight engine
 * with AI-style UX. Every recommendation answers: What's wrong? Why does
 * it matter? What should I do? How do I know it worked? — using the
 * business's own stored data as evidence.
 */
class RecommendationService
{
    /** Bottleneck → orchestrated capabilities (Telkomsel + TUMBUH-native). */
    public const SOLUTION_MAP = [
        'acquisition' => ['myads', 'kuncie', 'umkm_plus', 'digiads'],
        'conversion' => ['kuncie', 'myads', 'rcs'],
        'retention' => ['rcs', 'myads', 'kuncie'],
        'digital_presence' => ['umkm_plus', 'kuncie', 'myads'],
        'productivity' => ['digipos', 'soundbox', 'growth_finance', 'growth_inventory'],
        'financial_health' => ['growth_finance', 'soundbox', 'kuncie'],
        'inventory_health' => ['growth_inventory', 'digipos', 'kuncie'],
        'growth_readiness' => ['msight', 'sitesense', 'dce'],
        'expansion' => ['msight', 'sitesense', 'dce', 'digiads'],
    ];

    /** TUMBUH-native capabilities (not Telkomsel products). */
    public const NATIVE = [
        'growth_finance' => ['name' => 'Growth Finance', 'route' => 'finance.index', 'icon' => 'account_balance_wallet', 'role' => 'Kapabilitas native TUMBUH'],
        'growth_inventory' => ['name' => 'Growth Inventory', 'route' => 'inventory.index', 'icon' => 'inventory_2', 'role' => 'Kapabilitas native TUMBUH'],
    ];

    public function __construct(
        private KpiService $kpi,
        private FinancialAnalyticsService $finance,
        private InventoryAnalyticsService $inventory,
    ) {}

    /** Expansion mode (brief "Kondisi 4"): strong across the board. */
    public function isExpansionMode(GrowthScoreSnapshot $score): bool
    {
        return $score->overall >= 80 && $score->scoreFor($score->bottleneck) >= 70;
    }

    public function generate(Business $business, GrowthScoreSnapshot $score, string $kind = 'assistant'): Recommendation
    {
        $focus = $this->isExpansionMode($score) ? 'expansion' : $score->bottleneck;
        $content = $this->compose($business, $score, $focus);

        $business->recommendations()->where('kind', $kind)->update(['is_current' => false]);

        return $business->recommendations()->create([
            'growth_score_snapshot_id' => $score->id,
            'kind' => $kind,
            'bottleneck' => $focus,
            'headline' => $content['headline'],
            'whats_wrong' => $content['wrong'],
            'why_it_matters' => $content['why'],
            'what_to_do' => $content['do'],
            'success_metric' => $content['metric'],
            'evidence' => $content['evidence'],
            'solution_keys' => $this->matchSolutions($business, $focus),
            'review' => $content['review'] ?? null,
            'is_current' => true,
        ]);
    }

    /**
     * Ordered solution keys for a focus area. Solutions the business already
     * uses are moved to the end (shown as optimisation, not as new sales).
     */
    public function matchSolutions(Business $business, string $focus): array
    {
        $keys = self::SOLUTION_MAP[$focus] ?? [];
        $tierRank = $business->tierRank();
        $solutions = TelkomselSolution::whereIn('key', $keys)->get()->keyBy('key');

        return collect($keys)
            // Gold-only advanced tools are not pushed to Bronze businesses.
            ->reject(fn ($k) => isset($solutions[$k]) && $tierRank < 2 && $solutions[$k]->min_tier === 'gold' && $focus !== 'expansion')
            ->sortBy(fn ($k) => $this->alreadyUses($business, $k) ? 1 : 0)
            ->values()->all();
    }

    public function alreadyUses(Business $business, string $key): bool
    {
        if ($business->usesProduct($key)) {
            return true;
        }

        return $business->activations()->where('status', 'active')
            ->whereHas('solution', fn ($q) => $q->where('key', $key))->exists();
    }

    private function compose(Business $business, GrowthScoreSnapshot $score, string $focus): array
    {
        $baseline = $this->kpi->baseline($business);
        $latest = $this->kpi->latest($business);
        $previous = $this->kpi->previous($business);
        $dimScore = $focus === 'expansion' ? $score->overall : $score->scoreFor($focus);
        $trend = fn (string $m) => $previous && $latest ? $latest->{$m} - $previous->{$m} : null;

        switch ($focus) {
            case 'acquisition':
                $current = $latest?->new_customers ?? 0;
                $target = (int) $this->kpi->target($business, 'new_customers');
                $t = $trend('new_customers');

                return [
                    'headline' => 'Customer Acquisition adalah bottleneck utama Anda.',
                    'wrong' => 'Pelanggan baru Anda '.num($current).'/bulan, di bawah target '.num($target).'. Skor Akuisisi '.$dimScore.'/100 — terendah dari 8 dimensi.',
                    'why' => 'Tanpa arus pelanggan baru yang stabil, omzet sulit naik walaupun retensi bagus. Akuisisi adalah mesin pertumbuhan pertama.',
                    'do' => 'Pelajari strategi akuisisi lewat Kuncie (minggu 1), lalu jalankan kampanye berbasis lokasi dengan MyAds di radius 2 km dari toko (minggu 2).',
                    'metric' => 'Pelanggan baru naik dari '.num($current).' menuju '.num($target).' per bulan dalam 30 hari.',
                    'evidence' => array_values(array_filter([
                        ['label' => 'Pelanggan baru / bulan', 'value' => num($current), 'tone' => 'bad'],
                        ['label' => 'Target', 'value' => num($target), 'tone' => 'neutral'],
                        $t !== null ? ['label' => 'Tren vs bulan lalu', 'value' => signed($t), 'tone' => $t >= 0 ? 'good' : 'bad'] : ['label' => 'Skor Akuisisi', 'value' => $dimScore.'/100', 'tone' => 'bad'],
                    ])),
                ];

            case 'conversion':
                $current = $latest?->conversion_rate ?? 0;
                $target = $this->kpi->target($business, 'conversion_rate');
                $t = $trend('conversion_rate');
                $reachUp = $previous && $latest && $previous->campaign_reach > 0 ? ($latest->campaign_reach - $previous->campaign_reach) / $previous->campaign_reach * 100 : null;

                return [
                    'headline' => 'Conversion masih menjadi bottleneck Anda.',
                    'wrong' => 'Conversion rate '.pct($current).', di bawah target '.pct($target).'.'.($reachUp !== null && $reachUp > 10 ? ' Jangkauan naik '.pct($reachUp, 0).', tetapi pembeli belum ikut naik.' : ''),
                    'why' => 'Menambah budget iklan saat konversi rendah hanya memperbesar biaya per pembeli. Perbaiki penawaran dulu, baru perbesar jangkauan.',
                    'do' => 'Optimalkan penawaran dan pengalaman pemesanan (menu bundling, CTA WhatsApp yang jelas) sebelum menambah budget iklan. Ikuti modul Kuncie "Offer & Conversion".',
                    'metric' => 'Conversion naik dari '.pct($current).' menuju '.pct($target).'.',
                    'evidence' => array_values(array_filter([
                        ['label' => 'Conversion rate', 'value' => pct($current), 'tone' => 'bad'],
                        ['label' => 'Target', 'value' => pct($target), 'tone' => 'neutral'],
                        $t !== null ? ['label' => 'Tren', 'value' => signed($t, 1, 'pp'), 'tone' => $t > 0 ? 'good' : 'bad'] : null,
                    ])),
                ];

            case 'retention':
                $current = $latest?->repeat_rate ?? 0;
                $target = $this->kpi->target($business, 'repeat_rate');
                $goal = min($target, $current + 5);
                $t = $trend('repeat_rate');

                return [
                    'headline' => 'Customer Retention adalah bottleneck utama Anda.',
                    'wrong' => 'Repeat customer rate '.pct($current, 0).', di bawah target '.pct($target, 0).'.'.($t !== null && $t < 0 ? ' Turun '.pct(abs($t)).' bulan ini meski pelanggan baru bertambah.' : ''),
                    'why' => 'Pelanggan yang kembali membuat omzet lebih dapat diprediksi dan tidak membutuhkan biaya akuisisi yang sama.',
                    'do' => 'Luncurkan kampanye re-engagement untuk pelanggan yang belum kembali 14 hari terakhir: voucher kunjungan kedua via RCS/MyAds dan program loyalitas sederhana.',
                    'metric' => 'Repeat customer naik dari '.pct($current, 0).' menuju '.pct($goal, 0).' dalam 30 hari.',
                    'evidence' => array_values(array_filter([
                        ['label' => 'Repeat customer', 'value' => pct($current, 0), 'tone' => 'bad'],
                        ['label' => 'Target', 'value' => pct($target, 0), 'tone' => 'neutral'],
                        $t !== null ? ['label' => 'Tren', 'value' => signed($t, 1, 'pp'), 'tone' => $t >= 0 ? 'good' : 'bad'] : null,
                    ])),
                ];

            case 'digital_presence':
                $channels = count($business->sales_channels ?? []);

                return [
                    'headline' => 'Digital Presence Anda masih terbatas.',
                    'wrong' => 'Skor Kehadiran Digital '.$dimScore.'/100 dengan '.$channels.' kanal penjualan aktif.',
                    'why' => 'Pelanggan mencari dan memesan secara online. Kanal digital yang konsisten adalah fondasi semua aksi pemasaran berikutnya.',
                    'do' => 'Pastikan konektivitas bisnis stabil (UMKM Plus), lengkapi katalog di WhatsApp Business & Instagram, dan ikuti kelas dasar pemasaran digital di Kuncie.',
                    'metric' => 'Minimal 3 kanal digital aktif dan skor Kehadiran Digital ≥ 65.',
                    'evidence' => [
                        ['label' => 'Skor Kehadiran Digital', 'value' => $dimScore.'/100', 'tone' => 'bad'],
                        ['label' => 'Kanal aktif', 'value' => (string) $channels, 'tone' => 'neutral'],
                        ['label' => 'Target', 'value' => '≥ 3 kanal', 'tone' => 'neutral'],
                    ],
                ];

            case 'productivity':
                $share = $latest?->digital_txn_share ?? 0;

                return [
                    'headline' => 'Produktivitas operasional menahan pertumbuhan Anda.',
                    'wrong' => 'Baru '.pct($share, 0).' transaksi tercatat digital. Banyak pencatatan masih manual.',
                    'why' => 'Tanpa catatan digital, Anda tidak bisa mengukur apakah aksi pemasaran benar-benar menghasilkan omzet — dan waktu habis untuk administrasi.',
                    'do' => 'Catat penjualan lewat Growth Finance & Growth Inventory, aktifkan pembayaran QRIS dengan notifikasi Soundbox, dan gunakan DigiPOS untuk transaksi digital yang didukung.',
                    'metric' => 'Transaksi tercatat digital naik dari '.pct($share, 0).' menuju 70%.',
                    'evidence' => [
                        ['label' => 'Transaksi tercatat digital', 'value' => pct($share, 0), 'tone' => 'bad'],
                        ['label' => 'Target', 'value' => '70%', 'tone' => 'neutral'],
                        ['label' => 'Skor Produktivitas', 'value' => $dimScore.'/100', 'tone' => 'bad'],
                    ],
                ];

            case 'financial_health':
                $f = $this->finance->insight($business);

                return [
                    'headline' => 'Kesehatan keuangan adalah bottleneck Anda.',
                    'wrong' => $f['headline'],
                    'why' => 'Omzet yang naik tidak berarti untung naik. Margin yang tergerus membuat pertumbuhan tidak berkelanjutan.',
                    'do' => $f['action'],
                    'metric' => 'Gross margin kembali ke ≥ '.pct($f['target_margin'], 0).' dalam 30 hari.',
                    'evidence' => $f['evidence'],
                ];

            case 'inventory_health':
                $s = $this->inventory->stats($business);
                $risk = $this->inventory->insights($business)[0] ?? null;

                return [
                    'headline' => 'Inventaris sedang menghambat pertumbuhan Anda.',
                    'wrong' => num($s['low'] + $s['out']).' dari '.num($s['total']).' produk stoknya menipis atau habis.'.($risk ? ' '.$risk['text'] : ''),
                    'why' => 'Stok habis berarti penjualan hilang; stok lambat laku berarti kas tertahan. Keduanya menekan margin.',
                    'do' => 'Restock produk fast-moving berdasarkan estimasi hari-sampai-habis, dan buat promo bundling untuk produk slow-moving.',
                    'metric' => 'Produk stok menipis/habis turun ke ≤ 10% katalog dan tidak ada stockout produk utama.',
                    'evidence' => [
                        ['label' => 'Stok menipis', 'value' => num($s['low']), 'tone' => 'bad'],
                        ['label' => 'Stok habis', 'value' => num($s['out']), 'tone' => 'bad'],
                        ['label' => 'Nilai inventaris', 'value' => rupiah_short($s['value']), 'tone' => 'neutral'],
                    ],
                ];

            case 'growth_readiness':
            case 'expansion':
            default:
                $revGrowth = $baseline && $latest && $baseline->revenue > 0 ? ($latest->revenue - $baseline->revenue) / $baseline->revenue * 100 : 0;

                return [
                    'headline' => $focus === 'expansion' ? 'Bisnis Anda mungkin siap ekspansi.' : 'Kesiapan bertumbuh perlu diperkuat.',
                    'wrong' => $focus === 'expansion'
                        ? 'Growth Score '.$score->overall.'/100 dan tidak ada dimensi di bawah 70. Pertanyaannya bukan lagi "bagaimana mendapat pelanggan pertama" tetapi "di mana ekspansi berikutnya".'
                        : 'Skor Kesiapan Bertumbuh '.$dimScore.'/100.',
                    'why' => 'Ekspansi tanpa data lokasi dan pasar berisiko tinggi. Keputusan berbasis data menekan risiko gagal outlet baru.',
                    'do' => 'Jalankan Expansion Readiness, gunakan MSIGHT untuk memahami konsentrasi pelanggan, lalu SiteSense untuk memilih lokasi. Cek kelayakan DCE pathway.',
                    'metric' => 'Expansion Readiness ≥ 80 dan satu lokasi kandidat tervalidasi.',
                    'evidence' => [
                        ['label' => 'Growth Score', 'value' => $score->overall.'/100', 'tone' => 'good'],
                        ['label' => 'Pertumbuhan omzet', 'value' => signed($revGrowth, 1, '%'), 'tone' => $revGrowth >= 0 ? 'good' : 'bad'],
                        ['label' => 'Dimensi terendah', 'value' => $score->scoreFor($score->bottleneck).'/100', 'tone' => 'neutral'],
                    ],
                ];
        }
    }

    /**
     * Assistant Q&A: preset business questions answered from stored data.
     * Returns null for unknown intents.
     */
    public function answer(Business $business, string $intent): ?array
    {
        $latest = $this->kpi->latest($business);
        $previous = $this->kpi->previous($business);
        $score = $business->latestScore;
        if (! $latest || ! $score) {
            return null;
        }

        $pct = fn ($a, $b) => $b > 0 ? ($a - $b) / $b * 100 : 0;

        return match ($intent) {
            'sales_drop' => (function () use ($latest, $previous, $pct, $business) {
                if (! $previous) {
                    return ['q' => 'Penjualan saya turun. Apa yang harus saya lakukan?', 'a' => ['Belum ada data bulan sebelumnya untuk dibandingkan. Catat KPI bulan ini agar saya bisa menganalisis tren.'], 'cta' => ['label' => 'Buka Growth Ledger', 'url' => route('growth.ledger')]];
                }
                $rev = $pct($latest->revenue, $previous->revenue);
                $new = $pct($latest->new_customers, $previous->new_customers);
                $rep = $latest->repeat_rate - $previous->repeat_rate;
                $lines = ['Omzet bulan ini '.signed($rev, 1, '%').' dibanding bulan lalu ('.rupiah_short($previous->revenue).' → '.rupiah_short($latest->revenue).').'];
                $lines[] = 'Pelanggan baru '.signed($new, 1, '%').', repeat customer '.signed($rep, 1, 'pp').'.';
                if ($rep < 0 && $new >= 0) {
                    $lines[] = 'Akuisisi stabil, tetapi pembelian ulang menurun. Rekomendasi: kampanye re-engagement (RCS / MyAds) untuk pelanggan lama.';
                    $cta = ['label' => 'Lihat solusi retensi', 'url' => route('solutions.index', ['focus' => 'retention'])];
                } elseif ($new < 0) {
                    $lines[] = 'Arus pelanggan baru melambat. Rekomendasi: kampanye lokasi MyAds di radius 2 km.';
                    $cta = ['label' => 'Buka MyAds', 'url' => route('campaigns.index')];
                } else {
                    $lines[] = 'Tidak ada penurunan signifikan pada akuisisi maupun retensi. Fokus pada bottleneck saat ini: '.config("tumbuh.dimensions.{$business->latestScore->bottleneck}.label").'.';
                    $cta = ['label' => 'Lihat rencana aksi', 'url' => route('plan.show')];
                }

                return ['q' => 'Penjualan saya turun. Apa yang harus saya lakukan?', 'a' => $lines, 'cta' => $cta];
            })(),
            'focus' => [
                'q' => 'Apa yang harus saya fokuskan bulan ini?',
                'a' => [
                    'Bottleneck Anda: '.config("tumbuh.dimensions.{$score->bottleneck}.label").' ('.$score->scoreFor($score->bottleneck).'/100).',
                    'Area terkuat: '.config("tumbuh.dimensions.{$score->strongest}.label").' ('.$score->scoreFor($score->strongest).'/100) — pertahankan.',
                    'Selesaikan misi minggu ini sebelum menambah inisiatif baru.',
                ],
                'cta' => ['label' => 'Lihat misi minggu ini', 'url' => route('missions.index')],
            ],
            'profit' => (function () use ($business) {
                $f = $this->finance->insight($business);

                return ['q' => 'Apakah bisnis saya benar-benar untung?', 'a' => [$f['headline'], $f['action']], 'cta' => ['label' => 'Buka laporan keuangan', 'url' => route('finance.report')]];
            })(),
            'stock' => (function () use ($business) {
                $insights = $this->inventory->insights($business);
                $lines = $insights ? collect($insights)->take(3)->pluck('text')->all() : ['Belum ada data produk. Tambahkan produk di Growth Inventory.'];

                return ['q' => 'Produk mana yang perlu saya restock?', 'a' => $lines, 'cta' => ['label' => 'Buka Growth Inventory', 'url' => route('inventory.index')]];
            })(),
            default => null,
        };
    }
}
