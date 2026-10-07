<?php

namespace App\Services;

use App\Models\ActionPlan;
use App\Models\Business;
use App\Models\Recommendation;
use Illuminate\Support\Facades\DB;

/**
 * Personalised 30-Day Action Plan: Understand → Activate → Optimize → Prove.
 * Each week becomes an actionable Weekly Growth Mission.
 */
class ActionPlanService
{
    public function __construct(private KpiService $kpi) {}

    public function generate(Business $business, Recommendation $recommendation): ActionPlan
    {
        return DB::transaction(function () use ($business, $recommendation) {
            $focus = $recommendation->bottleneck;
            $tpl = $this->template($business, $focus);
            $latest = $this->kpi->latest($business);
            $metric = $tpl['metric'];
            $baselineValue = $metric && $latest ? (float) $latest->{$metric} : null;
            $target = $metric ? $this->targetFor($business, $metric, $baselineValue) : null;

            $business->actionPlans()->where('status', 'active')->update(['status' => 'superseded']);
            $business->missions()->whereIn('status', ['upcoming', 'active'])->delete();

            $plan = $business->actionPlans()->create([
                'recommendation_id' => $recommendation->id,
                'cycle' => $business->growth_cycle,
                'bottleneck' => $focus,
                'title' => $tpl['title'],
                'goal' => $tpl['goal'],
                'kpi_metric' => $metric ?? 'growth_score',
                'kpi_baseline' => $baselineValue,
                'kpi_target' => $target,
                'starts_on' => now()->toDateString(),
                'status' => 'active',
            ]);

            foreach ($tpl['weeks'] as $i => $week) {
                $n = $i + 1;
                $item = $plan->items()->create([
                    'week' => $n,
                    'phase' => $week['phase'],
                    'title' => $week['title'],
                    'goal' => $week['goal'],
                    'actions' => $week['actions'],
                    'solution_key' => $week['solution'],
                    'kpi_target' => $week['kpi_target'],
                    'status' => $n === 1 ? 'active' : 'pending',
                ]);

                $business->missions()->create([
                    'action_plan_item_id' => $item->id,
                    'week' => $n,
                    'title' => $week['mission'],
                    'description' => $week['mission_detail'],
                    'kpi_metric' => $week['mission_metric'] ?? null,
                    'kpi_label' => $week['mission_kpi_label'] ?? null,
                    'kpi_target' => $week['mission_target'] ?? null,
                    'solution_key' => $week['solution'],
                    'due_on' => now()->addDays(7 * $n)->toDateString(),
                    'status' => $n === 1 ? 'active' : 'upcoming',
                    'points' => 10 + 5 * $n,
                ]);
            }

            return $plan->load('items');
        });
    }

    private function targetFor(Business $business, string $metric, ?float $current): ?float
    {
        if ($current === null) {
            return null;
        }

        return match ($metric) {
            'new_customers' => (float) ceil($current * 1.33),
            'conversion_rate' => round($current + 1.5, 1),
            'repeat_rate' => round($current + 5, 1),
            'gross_margin' => max(40.0, round($current + 4, 1)),
            'digital_txn_share' => 70.0,
            default => null,
        };
    }

    private function template(Business $business, string $focus): array
    {
        $plans = [
            'acquisition' => [
                'title' => '30-Day Customer Acquisition Plan',
                'goal' => 'Tambah pelanggan baru dari sekitar lokasi usaha',
                'metric' => 'new_customers',
                'weeks' => [
                    ['phase' => 'Understand', 'title' => 'Pahami strategi akuisisi', 'goal' => 'Tahu siapa calon pelanggan & dari mana mereka datang', 'actions' => ['Ikuti modul Kuncie "Customer Acquisition 101"', 'Tentukan 3 segmen calon pelanggan utama'], 'solution' => 'kuncie', 'kpi_target' => '3 segmen terdefinisi',
                        'mission' => 'Tentukan 3 segmen pelanggan teratas Anda', 'mission_detail' => 'Selesaikan modul Kuncie, lalu tulis 3 segmen calon pelanggan (mis. pekerja kantor radius 2 km, mahasiswa, keluarga akhir pekan).', 'mission_kpi_label' => 'Jumlah segmen terdefinisi', 'mission_target' => 3],
                    ['phase' => 'Activate', 'title' => 'Aktifkan kampanye lokasi', 'goal' => 'Jangkau calon pelanggan di sekitar toko', 'actions' => ['Aktifkan akun MyAds', 'Buat kampanye berbasis lokasi radius 2 km', 'Pilih audiens sesuai segmen minggu 1'], 'solution' => 'myads', 'kpi_target' => 'Kampanye berjalan, jangkauan ≥ 20.000',
                        'mission' => 'Luncurkan kampanye tertarget pertama Anda', 'mission_detail' => 'Gunakan MyAds untuk kampanye lokasi. Simpan budget awal kecil (Rp250–500 ribu) untuk uji pesan.', 'mission_metric' => 'campaign_reach', 'mission_kpi_label' => 'Jangkauan kampanye', 'mission_target' => 20000],
                    ['phase' => 'Optimize', 'title' => 'Optimalkan audiens & pesan', 'goal' => 'Turunkan biaya per pelanggan baru', 'actions' => ['Review hasil kampanye (reach, klik, leads)', 'Sesuaikan audiens dan pesan penawaran'], 'solution' => 'myads', 'kpi_target' => 'Conversion leads → pembeli naik',
                        'mission' => 'Review performa kampanye', 'mission_detail' => 'Bandingkan reach, klik, dan leads. Ganti pesan yang paling lemah dan perketat audiens.', 'mission_metric' => 'leads', 'mission_kpi_label' => 'Leads dari kampanye', 'mission_target' => 150],
                    ['phase' => 'Prove', 'title' => 'Buktikan dampaknya', 'goal' => 'Bandingkan KPI dengan baseline', 'actions' => ['Laporkan KPI bulanan', 'Bandingkan pelanggan baru vs baseline di Growth Ledger'], 'solution' => null, 'kpi_target' => '+33% pelanggan baru',
                        'mission' => 'Bandingkan pelanggan baru dengan baseline', 'mission_detail' => 'Isi KPI bulan ini. TUMBUH akan menghitung Before vs After dan memperbarui Growth Score.', 'mission_metric' => 'new_customers', 'mission_kpi_label' => 'Pelanggan baru bulan ini', 'mission_target' => null],
                ],
            ],
            'conversion' => [
                'title' => '30-Day Conversion Plan',
                'goal' => 'Ubah lebih banyak calon pembeli menjadi pembeli',
                'metric' => 'conversion_rate',
                'weeks' => [
                    ['phase' => 'Understand', 'title' => 'Pahami titik bocor konversi', 'goal' => 'Tahu di mana calon pembeli berhenti', 'actions' => ['Modul Kuncie "Offer & Conversion"', 'Catat 20 chat terakhir yang tidak jadi membeli'], 'solution' => 'kuncie', 'kpi_target' => 'Daftar 3 alasan batal beli',
                        'mission' => 'Identifikasi 3 alasan calon pembeli batal', 'mission_detail' => 'Baca 20 percakapan terakhir yang tidak berujung pembelian dan kelompokkan alasannya.', 'mission_kpi_label' => 'Alasan teridentifikasi', 'mission_target' => 3],
                    ['phase' => 'Activate', 'title' => 'Perbaiki penawaran', 'goal' => 'Penawaran yang lebih mudah diterima', 'actions' => ['Buat menu bundling / harga coba', 'Tambahkan CTA pemesanan WhatsApp yang jelas'], 'solution' => 'kuncie', 'kpi_target' => 'Penawaran baru live',
                        'mission' => 'Luncurkan penawaran baru', 'mission_detail' => 'Uji satu bundling atau harga coba untuk pembeli pertama.', 'mission_kpi_label' => 'Penawaran aktif', 'mission_target' => 1],
                    ['phase' => 'Optimize', 'title' => 'Arahkan iklan ke penawaran baru', 'goal' => 'Leads berkualitas lebih tinggi', 'actions' => ['Perbarui materi kampanye MyAds', 'Retarget pengunjung yang belum membeli'], 'solution' => 'myads', 'kpi_target' => 'Conversion +1pp',
                        'mission' => 'Perbarui kampanye ke penawaran baru', 'mission_detail' => 'Ganti materi kampanye agar mengarah ke penawaran baru dan pantau konversi leads.', 'mission_metric' => 'conversion_rate', 'mission_kpi_label' => 'Conversion rate (%)', 'mission_target' => null],
                    ['phase' => 'Prove', 'title' => 'Buktikan dampaknya', 'goal' => 'Bandingkan conversion dengan baseline', 'actions' => ['Laporkan KPI bulanan', 'Bandingkan conversion vs baseline'], 'solution' => null, 'kpi_target' => 'Conversion menuju target',
                        'mission' => 'Bandingkan conversion dengan baseline', 'mission_detail' => 'Isi KPI bulan ini untuk Before vs After.', 'mission_metric' => 'conversion_rate', 'mission_kpi_label' => 'Conversion rate (%)', 'mission_target' => null],
                ],
            ],
            'retention' => [
                'title' => '30-Day Customer Retention Plan',
                'goal' => 'Tingkatkan pelanggan yang kembali membeli',
                'metric' => 'repeat_rate',
                'weeks' => [
                    ['phase' => 'Understand', 'title' => 'Pahami retensi pelanggan', 'goal' => 'Tahu siapa yang tidak kembali dan mengapa', 'actions' => ['Modul Kuncie "Customer Retention Strategy"', 'Kelompokkan pelanggan: baru, aktif, tidak aktif 14 hari'], 'solution' => 'kuncie', 'kpi_target' => 'Segmen pelanggan terdefinisi',
                        'mission' => 'Definisikan segmen pelanggan yang perlu diajak kembali', 'mission_detail' => 'Gunakan daftar kontak/transaksi untuk memisahkan pelanggan aktif dan tidak aktif.', 'mission_kpi_label' => 'Pelanggan tidak aktif teridentifikasi', 'mission_target' => 100],
                    ['phase' => 'Activate', 'title' => 'Aktifkan kampanye re-engagement', 'goal' => 'Ajak pelanggan lama kembali', 'actions' => ['Kirim voucher kunjungan kedua (RCS / MyAds)', 'Tentukan audiens: pelanggan tidak aktif 14 hari'], 'solution' => 'rcs', 'kpi_target' => '+5% repeat customer',
                        'mission' => 'Luncurkan kampanye re-engagement', 'mission_detail' => 'Kirim pesan dengan voucher kunjungan kedua ke pelanggan tidak aktif.', 'mission_metric' => 'campaign_reach', 'mission_kpi_label' => 'Pelanggan dijangkau', 'mission_target' => 300],
                    ['phase' => 'Optimize', 'title' => 'Optimalkan pesan & audiens', 'goal' => 'Respons pesan lebih tinggi', 'actions' => ['Review tingkat penukaran voucher', 'Ubah pesan untuk segmen yang tidak merespons'], 'solution' => 'myads', 'kpi_target' => 'Penukaran voucher ≥ 10%',
                        'mission' => 'Review penukaran voucher', 'mission_detail' => 'Hitung voucher yang ditukar dan perbaiki pesan untuk segmen dengan respons terendah.', 'mission_kpi_label' => 'Voucher ditukar', 'mission_target' => 30],
                    ['phase' => 'Prove', 'title' => 'Buktikan dampaknya', 'goal' => 'Bandingkan repeat customer dengan baseline', 'actions' => ['Ukur repeat customer bulan ini', 'Bandingkan dengan baseline'], 'solution' => null, 'kpi_target' => 'Repeat customer menuju target',
                        'mission' => 'Ukur repeat customer dan bandingkan dengan baseline', 'mission_detail' => 'Isi KPI bulan ini untuk Before vs After.', 'mission_metric' => 'repeat_rate', 'mission_kpi_label' => 'Repeat customer (%)', 'mission_target' => null],
                ],
            ],
            'financial_health' => [
                'title' => '30-Day Margin Recovery Plan',
                'goal' => 'Kembalikan margin kotor yang tergerus biaya bahan baku',
                'metric' => 'gross_margin',
                'weeks' => [
                    ['phase' => 'Understand', 'title' => 'Temukan cost driver', 'goal' => 'Tahu biaya mana yang paling naik', 'actions' => ['Catat semua pengeluaran via scan struk', 'Review 3 cost driver teratas di laporan'], 'solution' => 'growth_finance', 'kpi_target' => '100% pengeluaran tercatat',
                        'mission' => 'Catat semua pengeluaran minggu ini', 'mission_detail' => 'Gunakan Scan Struk agar semua biaya bahan baku tercatat dan terkategori.', 'mission_kpi_label' => 'Struk tercatat', 'mission_target' => 10],
                    ['phase' => 'Activate', 'title' => 'Negosiasi & substitusi bahan', 'goal' => 'Turunkan biaya bahan per unit', 'actions' => ['Bandingkan 2 pemasok untuk bahan termahal', 'Uji resep dengan substitusi bahan'], 'solution' => 'kuncie', 'kpi_target' => 'Biaya bahan −5%',
                        'mission' => 'Bandingkan harga 2 pemasok bahan baku utama', 'mission_detail' => 'Minta penawaran dari pemasok kedua untuk bahan dengan kenaikan terbesar.', 'mission_kpi_label' => 'Penghematan per bulan (Rp)', 'mission_target' => 500000],
                    ['phase' => 'Optimize', 'title' => 'Sesuaikan harga & bundling', 'goal' => 'Margin per produk sehat', 'actions' => ['Naikkan harga produk margin terendah secara bertahap', 'Bundling produk margin tinggi'], 'solution' => 'growth_inventory', 'kpi_target' => 'Margin produk utama ≥ 40%',
                        'mission' => 'Perbaiki harga produk dengan margin terendah', 'mission_detail' => 'Gunakan analisis margin per produk di Growth Inventory.', 'mission_kpi_label' => 'Produk disesuaikan', 'mission_target' => 3],
                    ['phase' => 'Prove', 'title' => 'Buktikan dampaknya', 'goal' => 'Bandingkan margin dengan baseline', 'actions' => ['Tutup buku bulanan', 'Bandingkan gross margin vs bulan lalu'], 'solution' => null, 'kpi_target' => 'Gross margin ≥ 40%',
                        'mission' => 'Tutup buku dan bandingkan margin', 'mission_detail' => 'Laporan laba rugi otomatis akan menghitung margin bulan ini.', 'mission_metric' => 'gross_margin', 'mission_kpi_label' => 'Gross margin (%)', 'mission_target' => null],
                ],
            ],
            'inventory_health' => [
                'title' => '30-Day Inventory Intelligence Plan',
                'goal' => 'Inventaris yang mendukung, bukan menghambat, pertumbuhan',
                'metric' => null,
                'weeks' => [
                    ['phase' => 'Understand', 'title' => 'Rapikan katalog & stok', 'goal' => 'Stok tercatat akurat', 'actions' => ['Lengkapi stok minimum semua produk', 'Stock opname produk utama'], 'solution' => 'growth_inventory', 'kpi_target' => '100% produk punya stok minimum',
                        'mission' => 'Lakukan stock opname produk utama', 'mission_detail' => 'Cocokkan stok fisik dengan catatan Growth Inventory.', 'mission_kpi_label' => 'Produk dicek', 'mission_target' => 10],
                    ['phase' => 'Activate', 'title' => 'Restock produk fast-moving', 'goal' => 'Tidak ada stockout produk utama', 'actions' => ['Restock produk dengan estimasi habis < 5 hari'], 'solution' => 'growth_inventory', 'kpi_target' => '0 stockout produk utama',
                        'mission' => 'Restock produk yang hampir habis', 'mission_detail' => 'Ikuti rekomendasi restock AI berdasarkan kecepatan penjualan.', 'mission_kpi_label' => 'Produk direstock', 'mission_target' => 3],
                    ['phase' => 'Optimize', 'title' => 'Gerakkan produk slow-moving', 'goal' => 'Kas tidak tertahan di stok', 'actions' => ['Promo bundling produk slow-moving', 'Kurangi pembelian produk lambat laku'], 'solution' => 'myads', 'kpi_target' => 'Stok slow-moving −30%',
                        'mission' => 'Buat promo untuk produk slow-moving', 'mission_detail' => 'Bundling produk lambat laku dengan produk terlaris.', 'mission_kpi_label' => 'Unit slow-moving terjual', 'mission_target' => 20],
                    ['phase' => 'Prove', 'title' => 'Buktikan dampaknya', 'goal' => 'Bandingkan kesehatan inventaris', 'actions' => ['Recalculate Growth Score', 'Review profitabilitas per produk'], 'solution' => null, 'kpi_target' => 'Inventory Health ≥ 65',
                        'mission' => 'Review profitabilitas per produk', 'mission_detail' => 'Gunakan laporan inventaris untuk memutuskan produk yang dipertahankan.', 'mission_kpi_label' => 'Produk direview', 'mission_target' => 5],
                ],
            ],
            'productivity' => [
                'title' => '30-Day Digital Operations Plan',
                'goal' => 'Catat transaksi secara digital agar dampak bisa diukur',
                'metric' => 'digital_txn_share',
                'weeks' => [
                    ['phase' => 'Understand', 'title' => 'Mulai catat digital', 'goal' => 'Semua penjualan harian tercatat', 'actions' => ['Catat penjualan harian di Growth Finance'], 'solution' => 'growth_finance', 'kpi_target' => '7 hari tercatat',
                        'mission' => 'Catat penjualan 7 hari berturut-turut', 'mission_detail' => 'Masukkan pemasukan harian lewat Growth Finance.', 'mission_kpi_label' => 'Hari tercatat', 'mission_target' => 7],
                    ['phase' => 'Activate', 'title' => 'Aktifkan pembayaran digital', 'goal' => 'Lebih banyak transaksi non-tunai', 'actions' => ['Cek kelayakan Soundbox untuk QRIS', 'Daftar DigiPOS untuk transaksi digital yang didukung'], 'solution' => 'soundbox', 'kpi_target' => 'QRIS + notifikasi suara aktif',
                        'mission' => 'Aktifkan pembayaran QRIS dengan notifikasi', 'mission_detail' => 'Cek kelayakan Soundbox (perlu integrasi acquirer QRIS).', 'mission_kpi_label' => 'Transaksi QRIS minggu ini', 'mission_target' => 50],
                    ['phase' => 'Optimize', 'title' => 'Hubungkan stok & penjualan', 'goal' => 'Stok berkurang otomatis saat terjual', 'actions' => ['Masukkan produk ke Growth Inventory', 'Catat penjualan per produk'], 'solution' => 'growth_inventory', 'kpi_target' => 'Produk utama terdaftar',
                        'mission' => 'Daftarkan 10 produk utama', 'mission_detail' => 'Penjualan per produk akan otomatis mengurangi stok dan menghitung COGS.', 'mission_kpi_label' => 'Produk terdaftar', 'mission_target' => 10],
                    ['phase' => 'Prove', 'title' => 'Buktikan dampaknya', 'goal' => 'Bandingkan dengan baseline', 'actions' => ['Laporkan KPI bulanan'], 'solution' => null, 'kpi_target' => '70% transaksi tercatat digital',
                        'mission' => 'Laporkan porsi transaksi tercatat digital', 'mission_detail' => 'Isi KPI bulan ini.', 'mission_metric' => 'digital_txn_share', 'mission_kpi_label' => 'Transaksi tercatat digital (%)', 'mission_target' => 70],
                ],
            ],
            'expansion' => [
                'title' => '30-Day Expansion Readiness Plan',
                'goal' => 'Putuskan ekspansi berikutnya berbasis data',
                'metric' => null,
                'weeks' => [
                    ['phase' => 'Understand', 'title' => 'Ukur kesiapan ekspansi', 'goal' => 'Tahu apakah bisnis siap ekspansi', 'actions' => ['Jalankan Expansion Readiness', 'Review kapasitas operasional & budget'], 'solution' => null, 'kpi_target' => 'Readiness ≥ 80',
                        'mission' => 'Jalankan Expansion Readiness assessment', 'mission_detail' => 'Isi kapasitas, budget, dan pasar yang diinginkan.', 'mission_kpi_label' => 'Skor readiness', 'mission_target' => 80],
                    ['phase' => 'Activate', 'title' => 'Pahami pasar dengan MSIGHT', 'goal' => 'Tahu konsentrasi pelanggan potensial', 'actions' => ['Ajukan pertanyaan bisnis ke MSIGHT', 'Review insight segmen & area'], 'solution' => 'msight', 'kpi_target' => '1 insight pasar',
                        'mission' => 'Dapatkan insight pasar dari MSIGHT', 'mission_detail' => 'Tanyakan "Di mana pelanggan potensial saya?"', 'mission_kpi_label' => 'Insight diperoleh', 'mission_target' => 1],
                    ['phase' => 'Optimize', 'title' => 'Pilih lokasi dengan SiteSense', 'goal' => 'Lokasi kandidat tervalidasi', 'actions' => ['Masukkan kriteria lokasi', 'Bandingkan 3 lokasi kandidat'], 'solution' => 'sitesense', 'kpi_target' => 'Lokasi skor ≥ 80',
                        'mission' => 'Bandingkan 3 lokasi kandidat di SiteSense', 'mission_detail' => 'Gunakan kriteria budget, ukuran toko, dan target pelanggan.', 'mission_kpi_label' => 'Lokasi dibandingkan', 'mission_target' => 3],
                    ['phase' => 'Prove', 'title' => 'Siapkan jalur akselerasi', 'goal' => 'Dukungan untuk ekspansi', 'actions' => ['Cek kelayakan DCE pathway', 'Siapkan profil bisnis'], 'solution' => 'dce', 'kpi_target' => 'Aplikasi DCE terkirim',
                        'mission' => 'Cek kelayakan dan siapkan aplikasi DCE', 'mission_detail' => 'DCE berbasis seleksi — tidak ada jaminan diterima.', 'mission_kpi_label' => 'Aplikasi', 'mission_target' => 1],
                ],
            ],
        ];

        $plans['digital_presence'] = [
            'title' => '30-Day Digital Presence Plan',
            'goal' => 'Bangun kanal digital yang konsisten',
            'metric' => null,
            'weeks' => [
                ['phase' => 'Understand', 'title' => 'Dasar pemasaran digital', 'goal' => 'Paham kanal yang relevan', 'actions' => ['Modul Kuncie "Digital Marketing Fundamentals"'], 'solution' => 'kuncie', 'kpi_target' => 'Modul selesai',
                    'mission' => 'Selesaikan modul Digital Marketing Fundamentals', 'mission_detail' => '4 pelajaran • 45 menit di Kuncie.', 'mission_kpi_label' => 'Pelajaran selesai', 'mission_target' => 4],
                ['phase' => 'Activate', 'title' => 'Stabilkan konektivitas', 'goal' => 'Operasional online tanpa hambatan', 'actions' => ['Cek kelayakan & aktivasi UMKM Plus'], 'solution' => 'umkm_plus', 'kpi_target' => 'Paket aktif',
                    'mission' => 'Aktifkan konektivitas bisnis', 'mission_detail' => 'Cek kelayakan UMKM Plus melalui MyTelkomsel.', 'mission_kpi_label' => 'Paket aktif', 'mission_target' => 1],
                ['phase' => 'Optimize', 'title' => 'Lengkapi katalog digital', 'goal' => 'Pelanggan bisa memesan online', 'actions' => ['Katalog WhatsApp Business', 'Profil Instagram & marketplace'], 'solution' => null, 'kpi_target' => '3 kanal aktif',
                    'mission' => 'Aktifkan 3 kanal penjualan digital', 'mission_detail' => 'WhatsApp Business katalog, Instagram, dan satu marketplace.', 'mission_kpi_label' => 'Kanal aktif', 'mission_target' => 3],
                ['phase' => 'Prove', 'title' => 'Buktikan dampaknya', 'goal' => 'Ukur pesanan dari kanal digital', 'actions' => ['Laporkan KPI bulanan'], 'solution' => null, 'kpi_target' => 'Pesanan digital tercatat',
                    'mission' => 'Laporkan pesanan dari kanal digital', 'mission_detail' => 'Isi KPI bulan ini.', 'mission_metric' => 'new_customers', 'mission_kpi_label' => 'Pelanggan baru bulan ini', 'mission_target' => null],
            ],
        ];
        $plans['growth_readiness'] = $plans['expansion'];

        return $plans[$focus] ?? $plans['acquisition'];
    }
}
