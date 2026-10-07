<?php

namespace Database\Seeders;

use App\Models\DiagnosticQuestion;
use Illuminate\Database\Seeder;

/** Configurable Growth Diagnostic question bank (stored in the DB). */
class DiagnosticQuestionSeeder extends Seeder
{
    public static function questions(): array
    {
        $o = fn (array $pairs) => array_map(fn ($p) => ['value' => $p[0], 'label' => $p[1], 'score' => $p[2]], $pairs);

        return [
            // A. Acquisition
            ['key' => 'acq_new_customers', 'dimension' => 'acquisition', 'section' => 'Acquisition', 'type' => 'single', 'weight' => 1.5,
                'prompt' => 'Berapa pelanggan baru yang Anda dapatkan per bulan?', 'help' => 'Perkiraan saja sudah cukup.',
                'options' => $o([['lt20', 'Kurang dari 20', 15], ['20_50', '20 – 50', 35], ['50_100', '50 – 100', 50], ['100_200', '100 – 200', 65], ['gt200', 'Lebih dari 200', 85]])],
            ['key' => 'acq_channels', 'dimension' => 'acquisition', 'section' => 'Acquisition', 'type' => 'multi', 'weight' => 1,
                'prompt' => 'Dari mana pelanggan baru biasanya menemukan bisnis Anda?', 'help' => 'Pilih semua yang sesuai.',
                'options' => $o([['instagram', 'Instagram', 15], ['tiktok', 'TikTok', 15], ['whatsapp', 'WhatsApp', 10], ['marketplace', 'Marketplace', 15], ['store', 'Toko fisik / lewat depan toko', 10], ['recommendation', 'Rekomendasi dari mulut ke mulut', 10], ['advertising', 'Iklan berbayar', 20], ['other', 'Lainnya', 5]])],
            ['key' => 'acq_advertising', 'dimension' => 'acquisition', 'section' => 'Acquisition', 'type' => 'single', 'weight' => 1,
                'prompt' => 'Apakah bisnis Anda melakukan advertising?',
                'options' => $o([['never', 'Tidak pernah', 10], ['boost', 'Sesekali boost postingan', 35], ['regular', 'Rutin beriklan setiap bulan', 70], ['targeted', 'Rutin dengan targeting lokasi/audiens', 90]])],
            ['key' => 'acq_budget', 'dimension' => 'acquisition', 'section' => 'Acquisition', 'type' => 'single', 'weight' => 0.5,
                'prompt' => 'Berapa budget pemasaran per bulan?',
                'options' => $o([['zero', 'Belum ada budget', 10], ['lt500', '< Rp500 ribu', 35], ['500_2m', 'Rp500 ribu – 2 juta', 60], ['2m_5m', 'Rp2 – 5 juta', 75], ['gt5m', '> Rp5 juta', 85]])],
            // B. Conversion
            ['key' => 'conv_rate', 'dimension' => 'conversion', 'section' => 'Conversion', 'type' => 'single', 'weight' => 1.5,
                'prompt' => 'Dari 10 orang yang bertanya (chat/DM/datang), berapa yang akhirnya membeli?',
                'options' => $o([['le1', '1 orang atau kurang', 20], ['2_3', '2 – 3 orang', 45], ['4_5', '4 – 5 orang', 65], ['gt5', 'Lebih dari 5 orang', 85]])],
            ['key' => 'conv_tracking', 'dimension' => 'conversion', 'section' => 'Conversion', 'type' => 'single', 'weight' => 1,
                'prompt' => 'Apakah Anda mencatat calon pembeli (leads) yang bertanya?',
                'options' => $o([['no', 'Tidak dicatat', 15], ['chat', 'Hanya tersimpan di chat', 40], ['sheet', 'Dicatat di buku / spreadsheet', 65], ['crm', 'Menggunakan CRM / aplikasi', 90]])],
            // C. Retention
            ['key' => 'ret_repeat', 'dimension' => 'retention', 'section' => 'Retention', 'type' => 'single', 'weight' => 1.5,
                'prompt' => 'Berapa persen pelanggan Anda yang membeli lagi (repeat)?',
                'options' => $o([['lt10', 'Kurang dari 10%', 15], ['10_20', '10 – 20%', 35], ['20_30', '20 – 30%', 55], ['30_45', '30 – 45%', 75], ['gt45', 'Lebih dari 45%', 90]])],
            ['key' => 'ret_database', 'dimension' => 'retention', 'section' => 'Retention', 'type' => 'single', 'weight' => 1,
                'prompt' => 'Apakah Anda memiliki database pelanggan?',
                'options' => $o([['none', 'Tidak ada', 10], ['contacts', 'Hanya kontak di HP', 35], ['sheet', 'Spreadsheet pelanggan', 65], ['crm', 'CRM / program loyalitas', 90]])],
            ['key' => 'ret_engagement', 'dimension' => 'retention', 'section' => 'Retention', 'type' => 'single', 'weight' => 1,
                'prompt' => 'Apakah Anda melakukan customer engagement (menghubungi pelanggan lama)?',
                'options' => $o([['never', 'Tidak pernah', 10], ['sometimes', 'Sesekali broadcast promo', 40], ['monthly', 'Program rutin setiap bulan', 70], ['loyalty', 'Program loyalitas terukur', 90]])],
            // D. Productivity
            ['key' => 'prod_digital_txn', 'dimension' => 'productivity', 'section' => 'Productivity', 'type' => 'single', 'weight' => 1,
                'prompt' => 'Berapa banyak transaksi yang tercatat secara digital?',
                'options' => $o([['none', 'Hampir tidak ada', 10], ['lt30', 'Kurang dari 30%', 35], ['30_70', '30 – 70%', 60], ['gt70', 'Lebih dari 70%', 85]])],
            ['key' => 'prod_sales_system', 'dimension' => 'productivity', 'section' => 'Productivity', 'type' => 'single', 'weight' => 1,
                'prompt' => 'Bagaimana Anda mencatat penjualan?',
                'options' => $o([['book', 'Buku tulis / nota manual', 20], ['sheet', 'Spreadsheet', 50], ['pos', 'Aplikasi kasir (POS)', 80], ['pos_stock', 'Kasir terhubung dengan stok', 95]])],
            ['key' => 'prod_manual', 'dimension' => 'productivity', 'section' => 'Productivity', 'type' => 'single', 'weight' => 1,
                'prompt' => 'Seberapa banyak aktivitas operasional yang masih manual?',
                'options' => $o([['almost_all', 'Hampir semua', 15], ['most', 'Sebagian besar', 35], ['half', 'Sekitar setengah', 55], ['little', 'Sedikit saja', 80]])],
            // E. Digital marketing / presence
            ['key' => 'dp_presence', 'dimension' => 'digital_presence', 'section' => 'Digital Marketing', 'type' => 'single', 'weight' => 1.5,
                'prompt' => 'Seperti apa kehadiran online bisnis Anda saat ini?',
                'options' => $o([['none', 'Belum ada', 10], ['wa', 'Hanya WhatsApp', 30], ['social', 'Media sosial aktif', 60], ['multi', 'Media sosial + marketplace/website aktif', 85]])],
            ['key' => 'dm_ads_history', 'dimension' => 'digital_presence', 'section' => 'Digital Marketing', 'type' => 'single', 'weight' => 1,
                'prompt' => 'Apakah Anda pernah menjalankan digital advertising?',
                'options' => $o([['never', 'Belum pernah', 15], ['once', 'Pernah sekali-dua kali', 40], ['regular', 'Rutin', 70], ['targeted', 'Rutin dengan targeting & A/B test', 90]])],
            ['key' => 'dm_measure', 'dimension' => 'conversion', 'section' => 'Digital Marketing', 'type' => 'single', 'weight' => 1,
                'prompt' => 'Apakah hasil campaign diukur?',
                'options' => $o([['no', 'Tidak diukur', 15], ['likes', 'Melihat likes / views', 40], ['sales', 'Menghitung penjualan dari campaign', 75], ['roas', 'Menghitung ROAS / biaya per pembeli', 90]])],
            // Financial management
            ['key' => 'fin_records', 'dimension' => 'financial_health', 'section' => 'Financial', 'type' => 'single', 'weight' => 1,
                'prompt' => 'Bagaimana Anda mengelola catatan keuangan usaha?',
                'options' => $o([['mixed', 'Tercampur dengan uang pribadi', 10], ['notes', 'Catatan sederhana', 35], ['monthly', 'Rekap bulanan di spreadsheet', 65], ['app', 'Aplikasi dengan laporan laba rugi', 90]])],
            ['key' => 'fin_margin', 'dimension' => 'financial_health', 'section' => 'Financial', 'type' => 'single', 'weight' => 1,
                'prompt' => 'Apakah Anda tahu margin keuntungan setiap produk?',
                'options' => $o([['no', 'Tidak tahu', 15], ['estimate', 'Perkiraan kasar', 45], ['main', 'Tahu untuk produk utama', 70], ['all', 'Tahu untuk semua produk', 90]])],
            // Inventory
            ['key' => 'inv_tracking', 'dimension' => 'inventory_health', 'section' => 'Inventory', 'type' => 'single', 'weight' => 1,
                'prompt' => 'Bagaimana Anda memantau stok?',
                'options' => $o([['none', 'Tidak dipantau', 15], ['manual', 'Hitung manual sesekali', 40], ['sheet', 'Spreadsheet stok', 60], ['app', 'Aplikasi dengan alert stok', 90], ['na', 'Tidak relevan (bisnis jasa)', 70]])],
            ['key' => 'inv_stockout', 'dimension' => 'inventory_health', 'section' => 'Inventory', 'type' => 'single', 'weight' => 1,
                'prompt' => 'Seberapa sering produk kehabisan stok dalam sebulan?',
                'options' => $o([['often', 'Sering (> 4 kali)', 20], ['sometimes', 'Kadang (2 – 4 kali)', 50], ['rarely', 'Jarang (1 kali)', 75], ['never', 'Tidak pernah', 90]])],
            // F. Growth / expansion
            ['key' => 'gr_outlet', 'dimension' => 'growth_readiness', 'section' => 'Growth', 'type' => 'single', 'weight' => 1,
                'prompt' => 'Apakah Anda berencana membuka outlet baru?',
                'options' => $o([['no', 'Belum ada rencana', 35], ['consider', 'Mempertimbangkan 1–2 tahun lagi', 55], ['12m', 'Dalam 12 bulan', 70], ['budgeted', 'Sudah direncanakan & dianggarkan', 85]])],
            ['key' => 'gr_new_market', 'dimension' => 'growth_readiness', 'section' => 'Growth', 'type' => 'single', 'weight' => 1,
                'prompt' => 'Apakah Anda ingin masuk ke wilayah / pasar baru?',
                'options' => $o([['no', 'Belum', 35], ['online', 'Ya, lewat penjualan online', 55], ['city', 'Ya, kota terdekat', 70], ['region', 'Ya, sudah riset pasar', 85]])],
        ];
    }

    public function run(): void
    {
        foreach (self::questions() as $i => $q) {
            DiagnosticQuestion::updateOrCreate(['key' => $q['key']], $q + ['position' => $i + 1, 'active' => true]);
        }
    }
}
