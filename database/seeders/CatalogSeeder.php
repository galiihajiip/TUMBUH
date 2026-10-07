<?php

namespace Database\Seeders;

use App\Models\Reward;
use App\Models\TelkomselSolution;
use Illuminate\Database\Seeder;

/**
 * Telkomsel capability catalogue (orchestrated, not resold) and rewards.
 * Descriptions are positioning statements for the prototype; nothing
 * here claims a live integration.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $solutions = [
            ['key' => 'kuncie', 'name' => 'Kuncie', 'layer' => 'learn', 'role' => 'Learning enabler', 'icon' => 'school',
                'tagline' => 'Belajar strategi bisnis yang relevan dengan bottleneck Anda.',
                'description' => 'Learning plan, tips & trick, video tutor dan mentoring dari praktisi. TUMBUH menjadi recommendation layer — pembelajaran tetap berlangsung di Kuncie.',
                'helps' => ['Learning plan sesuai bottleneck', 'Video tutor & tips praktis', 'Rekomendasi mentor'],
                'objective' => 'UMKM tahu apa yang harus dilakukan sebelum mengeksekusi', 'metrics' => ['Modul selesai', 'Aksi diterapkan', 'Perubahan KPI setelah belajar'],
                'addresses' => ['acquisition', 'conversion', 'retention', 'digital_presence', 'financial_health'], 'min_tier' => 'bronze', 'pricing_label' => 'included', 'price_note' => 'Konten gratis; Premium & mentoring di tier lebih tinggi',
                'tier_access' => ['bronze' => 'Free content', 'silver' => 'Personalized learning plan', 'gold' => 'Premium & priority mentoring']],
            ['key' => 'umkm_plus', 'name' => 'UMKM Plus', 'layer' => 'enable', 'role' => 'Connectivity layer', 'icon' => 'cell_tower',
                'tagline' => 'Konektivitas khusus bisnis untuk operasional digital.',
                'description' => 'Paket konektivitas untuk produktivitas UMKM dengan aktivasi/perpanjangan mandiri melalui MyTelkomsel bagi pelanggan yang memenuhi ketentuan.',
                'helps' => ['Internet stabil untuk kasir & pesanan online', 'Kuota khusus aplikasi bisnis', 'Perpanjangan mandiri'],
                'objective' => 'Operasional digital tanpa hambatan konektivitas', 'metrics' => ['Status paket', 'Pemakaian kuota', 'Uptime operasional'],
                'addresses' => ['digital_presence', 'acquisition'], 'min_tier' => 'silver', 'pricing_label' => 'activation_required', 'price_note' => 'Paket berbayar; syarat & ketentuan berlaku',
                'tier_access' => ['bronze' => 'Connectivity assessment & rekomendasi', 'silver' => 'Perbandingan paket & aktivasi', 'gold' => 'Optimasi pemakaian']],
            ['key' => 'myads', 'name' => 'MyAds', 'layer' => 'market', 'role' => 'Acquisition engine', 'icon' => 'campaign',
                'tagline' => 'Jangkau pelanggan tertarget di sekitar bisnis Anda.',
                'description' => 'Promosi mandiri dengan targeting berdasarkan lokasi dan profil, termasuk radius tertentu, serta monitoring performa dan laporan kampanye.',
                'helps' => ['Target pelanggan', 'Promosi berbasis lokasi', 'Pengukuran kampanye'],
                'objective' => 'Meningkatkan akuisisi pelanggan baru', 'metrics' => ['Reach', 'Response / klik', 'Leads', 'Conversion', 'ROAS'],
                'addresses' => ['acquisition', 'conversion', 'retention'], 'min_tier' => 'silver', 'pricing_label' => 'usage_cost', 'price_note' => 'Saldo iklan (top up) ditanggung pengguna',
                'tier_access' => ['bronze' => 'Rekomendasi & simulator', 'silver' => 'Aktivasi & monitoring kampanye', 'gold' => 'Optimasi & strategi audiens']],
            ['key' => 'digiads', 'name' => 'DigiAds', 'layer' => 'market', 'role' => 'Advanced marketing intelligence', 'icon' => 'insights',
                'tagline' => 'Targeting lanjutan untuk bisnis yang siap scale.',
                'description' => 'Format messaging/display/rewards dengan audience targeting berbasis data, melalui proses konsultasi – perancangan – implementasi.',
                'helps' => ['Advanced targeting', 'Konsultasi kampanye', 'Closed-loop measurement'],
                'objective' => 'Akuisisi skala besar yang terukur', 'metrics' => ['Reach tertarget', 'Conversion', 'Offline action (closed-loop)'],
                'addresses' => ['acquisition'], 'min_tier' => 'gold', 'pricing_label' => 'usage_cost', 'price_note' => 'Biaya kampanye sesuai konsultasi',
                'tier_access' => ['bronze' => 'Tidak tersedia', 'silver' => 'Edukasi & eligibility', 'gold' => 'Advanced targeting & eksekusi']],
            ['key' => 'rcs', 'name' => 'RCS Business Messaging', 'layer' => 'market', 'role' => 'Retention & engagement engine', 'icon' => 'forum',
                'tagline' => 'Pesan bisnis interaktif untuk mengajak pelanggan kembali.',
                'description' => 'Rich business messaging dengan multimedia, interaktivitas dan identitas bisnis terverifikasi untuk engagement pelanggan.',
                'helps' => ['Pesan multimedia interaktif', 'Voucher kunjungan kedua', 'Pengukuran respons'],
                'objective' => 'Meningkatkan repeat customer', 'metrics' => ['Delivered', 'Response', 'Voucher ditukar', 'Repeat purchase'],
                'addresses' => ['retention', 'conversion'], 'min_tier' => 'gold', 'pricing_label' => 'usage_cost', 'price_note' => 'Biaya per pesan',
                'tier_access' => ['bronze' => 'Tidak tersedia', 'silver' => 'Rekomendasi & use-case planning', 'gold' => 'Kampanye RCS']],
            ['key' => 'digipos', 'name' => 'DigiPOS', 'layer' => 'operate', 'role' => 'Digital sales / transaction activity layer', 'icon' => 'point_of_sale',
                'tagline' => 'Aktivitas penjualan digital untuk use case yang didukung.',
                'description' => 'Aplikasi untuk menjual produk Telkomsel dan transaksi digital lain (pulsa, paket data, token listrik, voucher) dengan laporan penjualan. Bukan POS umum untuk semua SKU UMKM.',
                'helps' => ['Pendapatan tambahan dari produk digital', 'Laporan penjualan', 'Aktivitas transaksi tercatat'],
                'objective' => 'Transaksi digital yang tercatat dan terukur', 'metrics' => ['Transaksi digital', 'Omzet produk digital', 'Laporan penjualan'],
                'addresses' => ['productivity', 'inventory_health'], 'min_tier' => 'bronze', 'pricing_label' => 'activation_required', 'price_note' => 'Registrasi outlet diperlukan',
                'tier_access' => ['bronze' => 'Basic activity tracking', 'silver' => 'Sales tracking & report', 'gold' => 'Integrated measurement']],
            ['key' => 'soundbox', 'name' => 'Soundbox', 'layer' => 'operate', 'role' => 'Payment enablement layer', 'icon' => 'volume_up',
                'tagline' => 'Notifikasi suara setiap pembayaran QRIS masuk.',
                'description' => 'Notifikasi suara atas transaksi QRIS; membutuhkan integrasi dengan bank/fintech/QRIS acquirer dan perangkat dari mitra resmi.',
                'helps' => ['Konfirmasi pembayaran instan', 'Mengurangi bukti transfer palsu', 'Feed transaksi (jika acquirer mendukung)'],
                'objective' => 'Pembayaran digital yang cepat dan tercatat', 'metrics' => ['Transaksi QRIS', 'Waktu antre kasir', 'Rekonsiliasi'],
                'addresses' => ['productivity', 'financial_health'], 'min_tier' => 'silver', 'pricing_label' => 'eligibility_required', 'price_note' => 'Butuh QRIS & acquirer partner',
                'tier_access' => ['bronze' => 'Eligibility check', 'silver' => 'Aktivasi & monitoring', 'gold' => 'Transaction integration*']],
            ['key' => 'msight', 'name' => 'MSIGHT', 'layer' => 'scale', 'role' => 'Advanced business intelligence', 'icon' => 'query_stats',
                'tagline' => 'Dari data pasar menjadi keputusan bisnis.',
                'description' => 'Solusi telecommunication big-data insight melalui konsultasi → perancangan → uji coba → implementasi. Di prototipe ini ditampilkan sebagai Demo Insight dengan data simulasi.',
                'helps' => ['Profil pelanggan & lokasi', 'Potensi pasar', 'Pola permintaan'],
                'objective' => 'Keputusan pasar berbasis data', 'metrics' => ['Insight → keputusan', 'Akurasi target pasar'],
                'addresses' => ['growth_readiness'], 'min_tier' => 'gold', 'pricing_label' => 'eligibility_required', 'price_note' => 'Melalui konsultasi',
                'tier_access' => ['bronze' => 'Tidak tersedia', 'silver' => 'Konsultasi / eligibility', 'gold' => 'Advanced insight']],
            ['key' => 'sitesense', 'name' => 'SiteSense', 'layer' => 'scale', 'role' => 'Expansion & location intelligence', 'icon' => 'location_on',
                'tagline' => 'Pilih lokasi ekspansi dengan data, bukan tebakan.',
                'description' => 'Location intelligence untuk memilih lokasi bisnis. Di prototipe ini skor lokasi dihitung oleh mesin lokal dengan data simulasi.',
                'helps' => ['Analisis lokasi kandidat', 'Potensi pelanggan', 'Tingkat persaingan'],
                'objective' => 'Lokasi outlet baru yang tervalidasi', 'metrics' => ['Skor lokasi', 'Market fit', 'Budget fit'],
                'addresses' => ['growth_readiness'], 'min_tier' => 'gold', 'pricing_label' => 'eligibility_required', 'price_note' => 'Melalui konsultasi',
                'tier_access' => ['bronze' => 'Tidak tersedia', 'silver' => 'Readiness assessment', 'gold' => 'Expansion intelligence']],
            ['key' => 'dce', 'name' => 'DCE Pathway', 'layer' => 'scale', 'role' => 'High-touch growth accelerator', 'icon' => 'rocket_launch',
                'tagline' => 'Jalur akselerasi selektif untuk UMKM yang siap tumbuh.',
                'description' => 'Pembelajaran, mentoring, benchmarking dan pitching. DCE adalah jalur seleksi — bukan produk yang dapat dibeli, dan kelayakan tidak menjamin diterima.',
                'helps' => ['Mentoring & workshop', 'Benchmarking', 'Pitching & jejaring'],
                'objective' => 'Akselerasi pertumbuhan UMKM terpilih', 'metrics' => ['Eligibility score', 'Tahap seleksi'],
                'addresses' => ['growth_readiness'], 'min_tier' => 'silver', 'pricing_label' => 'eligibility_required', 'price_note' => 'Berbasis seleksi',
                'tier_access' => ['bronze' => 'Discover', 'silver' => 'Eligibility & aplikasi', 'gold' => 'Priority pathway']],
        ];

        foreach ($solutions as $i => $s) {
            TelkomselSolution::updateOrCreate(['key' => $s['key']], $s + ['position' => $i + 1]);
        }

        $rewards = [
            ['key' => 'silver_trial', 'name' => 'Free Silver 1 Bulan', 'type' => 'tier_upgrade', 'icon' => 'workspace_premium', 'criteria' => 'Bronze di Top 20% Monthly Growth Leaderboard',
                'description' => 'Akses fitur Silver (Action Plan personal, Growth Ledger, aktivasi MyAds) selama 30 hari.'],
            ['key' => 'gold_trial', 'name' => 'Free Gold 1 Bulan', 'type' => 'tier_upgrade', 'icon' => 'diamond', 'criteria' => 'Silver di Top 20% dengan Achievement Score ≥ 75',
                'description' => 'Akses MSIGHT, SiteSense dan advanced analytics selama 30 hari.'],
            ['key' => 'myads_credit', 'name' => 'Kredit MyAds Rp100.000', 'type' => 'credit', 'icon' => 'campaign', 'criteria' => 'Target achievement ≥ 50%',
                'description' => 'Saldo iklan untuk kampanye berikutnya (ilustrasi reward program).'],
            ['key' => 'kuncie_premium', 'name' => 'Kuncie Premium 1 Bulan', 'type' => 'learning', 'icon' => 'school', 'criteria' => '3 misi selesai dengan KPI membaik',
                'description' => 'Akses kelas premium dan learning plan lanjutan.'],
            ['key' => 'expert_mentoring', 'name' => 'Sesi Expert Mentoring', 'type' => 'mentoring', 'icon' => 'support_agent', 'criteria' => 'Growth Score naik ≥ 5 poin',
                'description' => '1 sesi 60 menit dengan mentor bisnis sesuai bottleneck.'],
            ['key' => 'growth_consultation', 'name' => 'Growth Consultation', 'type' => 'consultation', 'icon' => 'handshake', 'criteria' => 'Growth Passport mencapai Growing',
                'description' => 'Konsultasi strategi pertumbuhan 1:1.'],
            ['key' => 'market_insight_session', 'name' => 'Market Insight Session', 'type' => 'insight', 'icon' => 'query_stats', 'criteria' => 'Growth Score ≥ 80',
                'description' => 'Sesi pembacaan insight pasar untuk rencana ekspansi.'],
        ];

        foreach ($rewards as $r) {
            Reward::updateOrCreate(['key' => $r['key']], $r);
        }
    }
}
