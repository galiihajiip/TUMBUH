<?php

/*
|--------------------------------------------------------------------------
| TUMBUH — Telkomsel untuk UMKM Tumbuh
|--------------------------------------------------------------------------
| Product catalogues, option lists and tier rules. Business formulas live
| in app/Services; this file only holds configurable reference data.
*/

return [

    'brand' => [
        'name' => 'TUMBUH',
        'full_name' => 'Telkomsel untuk UMKM Tumbuh',
        'mechanism' => 'Growth Loop',
        // Drop the team's final logo at public/brand/tumbuh-logo.svg (or .png)
        // and set TUMBUH_LOGO=brand/tumbuh-logo.svg — the wordmark is replaced everywhere.
        'logo' => env('TUMBUH_LOGO'),
    ],

    'demo' => [
        'otp' => '123456',
        'otp_ttl_minutes' => 5,
        'otp_resend_seconds' => 30,
        'password' => 'tumbuh123',
        'enabled' => env('TUMBUH_DEMO_MODE', true),
    ],

    'tiers' => [
        'bronze' => ['label' => 'Bronze', 'theme' => 'BUILD', 'goal' => 'Diagnose → Learn → Track', 'rank' => 1, 'price' => 'Gratis'],
        'silver' => ['label' => 'Silver', 'theme' => 'GROW', 'goal' => 'Activate → Measure → Improve', 'rank' => 2, 'price' => 'Berbayar'],
        'gold' => ['label' => 'Gold', 'theme' => 'SCALE', 'goal' => 'Analyze → Optimize → Expand', 'rank' => 3, 'price' => 'Premium'],
    ],

    /*
     * Minimum tier per gated feature. Enforced server-side by the `tier`
     * route middleware and TierService::allows(); views only mirror it.
     */
    'features' => [
        'growth_ledger.full' => 'silver',
        'before_after' => 'silver',
        'performance_review.full' => 'silver',
        'action_plan.personalized' => 'silver',
        'finance.ocr' => 'silver',
        'finance.pnl' => 'silver',
        'finance.advanced' => 'gold',
        'inventory.value' => 'silver',
        'inventory.predictive' => 'gold',
        'solution.activate.silver' => 'silver',
        'solution.activate.gold' => 'gold',
        'campaigns' => 'silver',
        'expansion.basic' => 'silver',
        'expansion.advanced' => 'gold',
        'msight' => 'gold',
        'sitesense' => 'gold',
        'dce.apply' => 'silver',
        'dce.priority' => 'gold',
        'analytics.advanced' => 'gold',
    ],

    'feature_labels' => [
        'growth_ledger.full' => 'Growth Ledger lengkap',
        'before_after' => 'Before vs After',
        'performance_review.full' => 'AI Performance Review lengkap',
        'action_plan.personalized' => 'Personalized Action Plan',
        'finance.ocr' => 'Scan struk (OCR)',
        'finance.pnl' => 'Laporan Laba Rugi & Cash Flow',
        'finance.advanced' => 'Analisis margin & cost driver',
        'inventory.value' => 'Nilai inventaris, COGS & sales linkage',
        'inventory.predictive' => 'Prediksi permintaan & AI restock',
        'solution.activate.silver' => 'Aktivasi solusi (Silver)',
        'solution.activate.gold' => 'Aktivasi solusi lanjutan (Gold)',
        'campaigns' => 'Kampanye MyAds',
        'expansion.basic' => 'Expansion Readiness',
        'expansion.advanced' => 'Expansion Recommendation',
        'msight' => 'MSIGHT Market Intelligence',
        'sitesense' => 'SiteSense Location Intelligence',
        'dce.apply' => 'Pendaftaran DCE Pathway',
        'dce.priority' => 'DCE Priority Pathway',
        'analytics.advanced' => 'Advanced Analytics',
    ],

    'roles' => [
        'owner' => 'Business Owner',
        'manager' => 'Business Manager',
        'staff' => 'Employee / Staff',
    ],

    'categories' => [
        'Food & Beverage' => ['Kafe & Kopi', 'Bakery & Kue', 'Dessert', 'Restoran', 'Katering', 'Minuman'],
        'Fashion' => ['Pakaian Muslim', 'Batik & Wastra', 'Pakaian Kasual', 'Aksesoris', 'Sepatu & Tas'],
        'Beauty' => ['Skincare', 'Kosmetik', 'Salon & Barbershop', 'Spa'],
        'Retail' => ['Toko Kelontong', 'Elektronik', 'Peralatan Rumah', 'Toko Online'],
        'Services' => ['Laundry', 'Bengkel', 'Jasa Kebersihan', 'Logistik'],
        'Creative' => ['Kerajinan', 'Desain & Percetakan', 'Fotografi', 'Konten'],
        'Agriculture' => ['Hortikultura', 'Perikanan', 'Peternakan', 'Olahan Pangan'],
        'Education' => ['Kursus', 'Bimbingan Belajar', 'Pelatihan'],
        'Health' => ['Apotek', 'Klinik', 'Herbal'],
        'Other' => ['Lainnya'],
    ],

    'provinces' => [
        'Jawa Timur' => ['Surabaya', 'Malang', 'Sidoarjo', 'Gresik'],
        'DKI Jakarta' => ['Jakarta Selatan', 'Jakarta Barat', 'Jakarta Pusat'],
        'Jawa Barat' => ['Bandung', 'Bekasi', 'Bogor', 'Depok'],
        'Jawa Tengah' => ['Semarang', 'Solo', 'Yogyakarta'],
        'Banten' => ['Tangerang', 'Tangerang Selatan', 'Serang'],
        'Bali' => ['Denpasar', 'Badung'],
        'Sumatera Utara' => ['Medan'],
        'Sulawesi Selatan' => ['Makassar'],
    ],

    'revenue_ranges' => [
        'lt5' => ['label' => '< Rp5 juta', 'mid' => 3_000_000],
        '5_10' => ['label' => 'Rp5–10 juta', 'mid' => 7_500_000],
        '10_25' => ['label' => 'Rp10–25 juta', 'mid' => 17_500_000],
        '25_50' => ['label' => 'Rp25–50 juta', 'mid' => 37_500_000],
        '50_100' => ['label' => 'Rp50–100 juta', 'mid' => 75_000_000],
        '100p' => ['label' => 'Rp100 juta+', 'mid' => 120_000_000],
    ],

    'sales_channels' => [
        'store' => 'Toko Fisik', 'whatsapp' => 'WhatsApp', 'instagram' => 'Instagram', 'tiktok' => 'TikTok',
        'website' => 'Website', 'marketplace' => 'Marketplace', 'reseller' => 'Reseller', 'other' => 'Lainnya',
    ],

    'digital_tools' => [
        'social_media' => 'Media Sosial', 'marketplace' => 'Marketplace', 'qris' => 'QRIS', 'pos' => 'POS / Kasir Digital',
        'digital_ads' => 'Iklan Digital', 'customer_db' => 'Database Pelanggan', 'accounting' => 'Software Akuntansi',
        'crm' => 'CRM', 'telkomsel_tools' => 'Tools Telkomsel',
    ],

    'telkomsel_products' => [
        'mytelkomsel' => 'MyTelkomsel', 'umkm_plus' => 'UMKM Plus', 'myads' => 'MyAds', 'digipos' => 'DigiPOS',
        'soundbox' => 'Soundbox', 'kuncie' => 'Kuncie', 'other' => 'Lainnya', 'none' => 'Belum menggunakan',
    ],

    'goals' => [
        'increase_revenue' => ['label' => 'Increase Revenue', 'id' => 'Tingkatkan omzet', 'icon' => 'payments', 'metric' => 'revenue'],
        'more_customers' => ['label' => 'Get More Customers', 'id' => 'Tambah pelanggan baru', 'icon' => 'group_add', 'metric' => 'new_customers'],
        'repeat_customers' => ['label' => 'Increase Repeat Customers', 'id' => 'Tingkatkan pembelian ulang', 'icon' => 'autorenew', 'metric' => 'repeat_rate'],
        'improve_conversion' => ['label' => 'Improve Conversion', 'id' => 'Tingkatkan konversi', 'icon' => 'conversion_path', 'metric' => 'conversion_rate'],
        'improve_productivity' => ['label' => 'Improve Productivity', 'id' => 'Tingkatkan produktivitas', 'icon' => 'speed', 'metric' => null],
        'improve_marketing' => ['label' => 'Improve Marketing', 'id' => 'Perbaiki pemasaran', 'icon' => 'campaign', 'metric' => null],
        'customer_engagement' => ['label' => 'Improve Customer Engagement', 'id' => 'Perkuat engagement pelanggan', 'icon' => 'forum', 'metric' => null],
        'new_market' => ['label' => 'Expand to New Market', 'id' => 'Ekspansi ke pasar baru', 'icon' => 'storefront', 'metric' => null],
        'digital_adoption' => ['label' => 'Improve Digital Adoption', 'id' => 'Tingkatkan adopsi digital', 'icon' => 'devices', 'metric' => null],
    ],

    'dimensions' => [
        'digital_presence' => ['label' => 'Digital Presence', 'id' => 'Kehadiran Digital', 'weight' => 0.12],
        'acquisition' => ['label' => 'Customer Acquisition', 'id' => 'Akuisisi Pelanggan', 'weight' => 0.16],
        'conversion' => ['label' => 'Conversion', 'id' => 'Konversi Penjualan', 'weight' => 0.14],
        'retention' => ['label' => 'Customer Retention', 'id' => 'Retensi Pelanggan', 'weight' => 0.16],
        'productivity' => ['label' => 'Productivity', 'id' => 'Produktivitas Operasional', 'weight' => 0.12],
        'financial_health' => ['label' => 'Financial Health', 'id' => 'Kesehatan Keuangan', 'weight' => 0.10],
        'inventory_health' => ['label' => 'Inventory Health', 'id' => 'Kesehatan Inventaris', 'weight' => 0.08],
        'growth_readiness' => ['label' => 'Growth Readiness', 'id' => 'Kesiapan Bertumbuh', 'weight' => 0.12],
    ],

    'expense_categories' => [
        'raw_material' => 'Raw Material', 'inventory' => 'Inventory', 'marketing' => 'Marketing', 'transportation' => 'Transportation',
        'utilities' => 'Utilities', 'salary' => 'Salary', 'rent' => 'Rent', 'equipment' => 'Equipment', 'other' => 'Other',
    ],

    // Expense categories treated as cost of goods sold in the P&L.
    // 'inventory' purchases are cash-out only: their cost reaches COGS when
    // the units are sold (embedded `cogs` on inventory-linked income).
    'cogs_categories' => ['raw_material'],
    'non_pnl_expense_categories' => ['inventory'],

    'income_categories' => [
        'product_sales' => 'Penjualan Produk', 'service' => 'Jasa', 'catering' => 'Pesanan Katering / Partai', 'other' => 'Lainnya',
    ],

    'payment_methods' => [
        'cash' => 'Tunai', 'qris' => 'QRIS', 'transfer' => 'Transfer Bank', 'ewallet' => 'E-Wallet', 'marketplace' => 'Marketplace',
    ],

    'source_labels' => [
        'self_reported' => ['label' => 'Self-reported', 'style' => 'dashed'],
        'integrated_demo' => ['label' => 'Integrated (Demo)', 'style' => 'info'],
        'verified' => ['label' => 'Verified', 'style' => 'ok'],
        'estimated' => ['label' => 'Estimated', 'style' => 'warn'],
        'simulated' => ['label' => 'Simulated', 'style' => 'sim'],
        'imported' => ['label' => 'Imported', 'style' => 'dashed'],
    ],

    'passport_levels' => [
        'digital_ready' => 'Digital Ready',
        'digital_active' => 'Digital Active',
        'productive' => 'Productive',
        'growing' => 'Growing',
        'scaling' => 'Scaling',
        'go_global' => 'Go Global',
    ],

    'leaderboard_weights' => [
        'target_achievement' => 0.50,
        'mission_completion' => 0.25,
        'kpi_improvement' => 0.15,
        'data_discipline' => 0.10,
    ],

    'notification_types' => [
        'mission' => ['label' => 'Growth Mission', 'icon' => 'flag'],
        'performance' => ['label' => 'Performance', 'icon' => 'trending_up'],
        'warning' => ['label' => 'Warning', 'icon' => 'warning'],
        'ai' => ['label' => 'AI', 'icon' => 'auto_awesome'],
        'reward' => ['label' => 'Reward', 'icon' => 'redeem'],
        'opportunity' => ['label' => 'Opportunity', 'icon' => 'rocket_launch'],
    ],

    'consents' => [
        'marketing' => ['label' => 'Marketing consent', 'help' => 'Menerima info program, promo, dan undangan acara TUMBUH.'],
        'analytics' => ['label' => 'Data analytics consent', 'help' => 'Mengizinkan data bisnis Anda dianalisis untuk Growth Score dan rekomendasi AI.'],
        'partner' => ['label' => 'Partner integration consent', 'help' => 'Mengizinkan pertukaran data dengan layanan terhubung (mis. MyAds, DigiPOS, acquirer QRIS).'],
    ],
];
