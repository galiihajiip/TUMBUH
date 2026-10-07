# TUMBUH — Telkomsel untuk UMKM Tumbuh

<p align="center">
  <img src="public/favicon.svg" width="96" height="96" alt="TUMBUH Logo">
</p>

<p align="center">
  <strong>Platform Orkestrator Ekosistem Digital Telkomsel untuk Pertumbuhan Terukur UMKM Indonesia</strong>
</p>

<p align="center">
  <a href="#-architecture"><img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13"></a>
  <a href="#-architecture"><img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3+"></a>
  <a href="#-architecture"><img src="https://img.shields.io/badge/Tailwind_CSS-v4.0-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS v4"></a>
  <a href="#-architecture"><img src="https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=white" alt="Alpine.js"></a>
  <a href="#-architecture"><img src="https://img.shields.io/badge/SQLite-Database-003B57?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite"></a>
  <a href="#-docker-setup"><img src="https://img.shields.io/badge/Docker-Ready-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker Ready"></a>
  <a href="#-testing--verification"><img src="https://img.shields.io/badge/Tests-29%20Passed%20(228%20Assertions)-success?style=for-the-badge" alt="Tests"></a>
</p>

---

## 📌 Executive Summary

**TUMBUH** (*Telkomsel untuk UMKM Tumbuh*) adalah solusi prototipe fungsional (*functional prototype*) yang dikembangkan untuk **Telkomsel Business Case Competition**. 

TUMBUH memecahkan salah satu masalah paling mendasar dalam digitalisasi UMKM di Indonesia: **adopsi digital sering kali berhenti di tingkat instalasi tanpa memberikan dampak pertumbuhan bisnis yang nyata dan terukur**.

Sebagai **Growth Orchestration Layer**, TUMBUH menyatukan berbagai solusi terisolasi di bawah ekosistem Telkomsel ke dalam sebuah mekanisme tertutup (*closed-loop*) berulang yang disebut **Growth Loop**.

```
DISCOVER → SIGN UP → ONBOARD → GOAL → DIAGNOSE → SCORE → BOTTLENECK → AI RECOMMENDATION
→ ACTION PLAN → ACTIVATE → EXECUTE → MEASURE → REVIEW → IMPROVE → GROW → SCALE → REDIAGNOSE ↺
```

> ⚠️ **Catatan Lingkungan Demo:** Seluruh integrasi ekosistem Telkomsel (MyAds, Kuncie, UMKM Plus, Soundbox, DigiPOS, MSIGHT, SiteSense, DCE) berjalan secara deterministik melalui *prototype adapters* lokal (`app/Services/Telkomsel/`). Tidak ada kredensial atau data pelanggan riil yang digunakan.

---

## 🔄 Mekanisme "Growth Loop"

*Growth Loop* adalah mekanisme operasional di dalam platform TUMBUH yang terdiri dari 17 tahapan berkelanjutan:

```
┌────────────────────────────────────────────────────────────────────────┐
│ [1] DISCOVER ──▶ [2] DAFTAR ──▶ [3] ONBOARDING ──▶ [4] TARGET GOAL     │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
┌───────────────────────────────────▼────────────────────────────────────┐
│ [5] DIAGNOSTIK ──▶ [6] GROWTH SCORE ──▶ [7] BOTTLENECK ──▶ [8] AI MATCH│
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
┌───────────────────────────────────▼────────────────────────────────────┐
│ [9] 30-DAY PLAN ──▶ [10] AKTIVASI ──▶ [11] EKSEKUSI ──▶ [12] PENGUKURAN│
└───────────────────────────────────┬────────────────────────────────────┘
                                    │
┌───────────────────────────────────▼────────────────────────────────────┐
│ [13] REVIEW ──▶ [14] EVALUASI SKOR ──▶ [15] REWARDS ──▶ [16] SKALA     │
│        ▲                                                         │     │
│        └─────────────────── [17] REDIAGNOSTIK ↺ ─────────────────┘     │
└────────────────────────────────────────────────────────────────────────┘
```

1. **Growth Score (0–100):** Formulasi multidimensi berbasis 4 pilar bisnis: *Customer Acquisition* (30%), *Operational Efficiency* (25%), *Customer Retention* (25%), dan *Market Scaling* (20%).
2. **Deterministic Bottleneck Detection:** Menemukan kendala operasional utama UMKM secara akurat tanpa *AI hallucination*.
3. **Closed-Loop Action & Verification:** Mengaitkan aksi nyata (kampanye MyAds, pelatihan Kuncie) dengan metrik *Before vs After* pada Growth Ledger, menutup siklus 30 hari, dan memperbarui skor secara transparan.

---

## 🏗️ Arsitektur & Teknologi

TUMBUH dibangun dengan prinsip **Laravel 13 Monolith** yang ringan, modular, dan siap dijalankan secara lokal tanpa dependensi eksternal yang rumit.

```
Browser ────────────────▶ Laravel 13 (PHP 8.4) ────────────────▶ SQLite Database
                           ├─ Blade + Tailwind v4 + Alpine.js
                           ├─ app/Services/*            (Business logic & formulas)
                           ├─ app/Services/Telkomsel/*  (Mock integration adapters)
                           └─ app/Services/Demo/*       (Deterministic scenarios & reset)
```

| Lapisan | Komponen & Lokasi | Deskripsi |
|---|---|---|
| **Scoring Engine** | `app/Services/GrowthScoreService.php`<br>`app/Services/BottleneckService.php`<br>`app/Services/DiagnosticService.php` | Kalkulasi skor 4 pilar dan deteksi bottleneck otomatis |
| **Deterministic AI** | `app/Services/RecommendationService.php`<br>`app/Services/PerformanceReviewService.php`<br>`app/Services/ActionPlanService.php` | Mesin rekomendasi solusi terarah, penjelasan *"Why am I seeing this?"*, dan rencana aksi 30 hari |
| **Growth Ledger** | `app/Services/KpiService.php`<br>`app/Services/LeaderboardService.php`<br>`app/Services/PassportService.php` | Rekam jejak pertumbuhan, perbandingan *Before vs After*, dan Growth Passport rewards |
| **Native Operations** | `app/Services/FinanceService.php`<br>`app/Services/FinancialAnalyticsService.php`<br>`app/Services/InventoryService.php` | Modul internal **Growth Finance** (Laba Rugi, Cash Flow, OCR Struk) & **Growth Inventory** (Valuasi stok, alert menipis) |
| **Orkestrasi Telkomsel** | `app/Services/SolutionService.php`<br>`app/Services/Telkomsel/AdapterRegistry.php`<br>`app/Services/CampaignService.php` | State machine aktivasi solusi dan simulasi analitik kampanye MyAds |
| **Scale & Lokasi** | `app/Services/ExpansionReadinessService.php` | Penilaian kelayakan ekspansi cabang, SiteSense scoring, dan MSIGHT footfall intelligence |
| **Akses Berjenjang** | `config/tumbuh.php` → `TierService.php` | Pembatasan fitur server-side melalui middleware `tier:` (`Bronze`, `Silver`, `Gold`) |
| **Demo & Simulasi** | `app/Services/DemoSimulationService.php`<br>`app/Services/Demo/DemoScenarioBuilder.php` | Kontrol panel demo untuk trigger kampanye, penutupan siklus, dan reset skenario |

---

## 🌐 Integrasi Ekosistem Telkomsel

TUMBUH mengorkestrasi portofolio produk Telkomsel untuk mengatasi setiap bottleneck UMKM:

| Produk Telkomsel | Adapter Service | Bottleneck / Target | Peran dalam Platform |
|---|---|---|---|
| **MyAds** | `MyAdsAdapter` | Akuisisi Pelanggan | Kampanye SMS tertarget berbasis LBS (Location Based Advertising) |
| **Kuncie** | `KuncieAdapter` | Kompetensi & Edukasi | Modul pembelajaran terkurasi sesuai bottleneck aktif pemilik usaha |
| **UMKM Plus** | `UmkmPlusAdapter` | Konektivitas Internet | Paket internet bisnis dedicated dan kuota khusus operasional |
| **DigiPOS** | `DigiPosAdapter` | Distribusi & Kasir | Distribusi digital produk agregasi dan kemitraan outlet |
| **Soundbox** | `SoundboxAdapter` | Operasional Pembayaran | Notifikasi suara real-time saat transaksi QRIS berhasil diterima |
| **DigiAds** | `DigiAdsAdapter` | Periklanan Lanjutan | Solusi periklanan terpadu multi-kanal (display & programmatic) |
| **RCS** | `RcsAdapter` | Retensi Pelanggan | Pesan bisnis interaktif dua arah (Rich Communication Services) |
| **MSIGHT** | `MSightAdapter` | Ekspansi Pasar | Analitik mobilitas dan kepadatan demografi berbasis Big Data Telkomsel |
| **SiteSense** | `SiteSenseAdapter` | Lokasi Usaha | Rekomendasi geospasial lokasi gerai baru berdasarkan daya beli kawasan |
| **DCE** | `DceAdapter` | Akselerasi Skala | Jalur seleksi dan pendampingan *Digital Creative Entrepreneurs* |

---

## 🚀 Panduan Instalasi & Menjalankan

### Opsi A: Menjalankan dengan Docker (Direkomendasikan)

Pastikan Docker Desktop atau Docker Engine telah terpasang di komputer Anda.

```bash
# 1. Clone repositori
git clone https://github.com/galiihajiip/TUMBUH.git
cd TUMBUH

# 2. Jalankan container (Otomatis migrasi database, seeding, dan build assets)
docker compose up --build
```

Buka peramban di **[http://localhost:8000](http://localhost:8000)**. Database SQLite dan file unggahan akan tersimpan persisten di dalam Docker Volume `tumbuh-data`.

Untuk mengganti port: `APP_PORT=8080 docker compose up --build`.

---

### Opsi B: Menjalankan Secara Lokal (Tanpa Docker)

**Kebutuhan Sistem:**
- PHP ≥ 8.3 dengan ekstensi `pdo_sqlite`, `curl`, `mbstring`
- Composer 2.x
- Node.js 20+ & npm

```bash
# 1. Install dependensi backend
composer install

# 2. Salin environment dan generate application key
cp .env.example .env
php artisan key:generate

# 3. Siapkan database SQLite dan seed data demo
touch database/database.sqlite
php artisan migrate:fresh --seed

# 4. Install dependensi frontend dan bangun asset
npm install
npm run build

# 5. Jalankan server lokal
php artisan serve
```

Aplikasi siap diakses di **[http://localhost:8000](http://localhost:8000)**.

---

## 👥 Akun Skenario Demo

Platform menyediakan **4 Persona UMKM Terverifikasi** dengan data deterministik untuk kebutuhan presentasi:

| Email Akun | Nama Usaha | Kategori & Kota | Tier | Narasi Skenario Demo |
|---|---|---|---|---|
| `aisha@tumbuh.demo` | **Aisha Dessert House** | Dessert, Malang | **Bronze** | **Tahap Awal:** Skor 34, bottleneck akuisisi pelanggan, operasional masih manual. |
| `chacha@tumbuh.demo` | **Chacha Coffee** | Kafe, Surabaya | **Silver** | **Showcase Closed-Loop Utama:** Baseline skor 64, bottleneck akuisisi → aktivasi MyAds → eksekusi misi → tutup siklus 30 hari → skor naik menjadi 74 (bottleneck berpindah ke retensi). |
| `sari@tumbuh.demo` | **Sari Bakery** | Bakery, Surabaya | **Silver** | **Tantangan Margin & Stok:** Penjualan tinggi tetapi margin tergerus (54% → 46%), stok bahan baku menipis. |
| `bima@tumbuh.demo` | **Nusantara Fashion** | Fashion, Surabaya | **Gold** | **Fase Skala & Ekspansi:** Skor 88, evaluasi kesiapan buka cabang baru di Surabaya melalui SiteSense & MSIGHT, serta lolos kriteria DCE. |

> 🔑 **Kredensial Universal:**
> - Password semua akun: **`tumbuh123`**
> - Kode OTP Demo: **`123456`**
> - Akses cepat tersedia melalui menu *Demo Accounts* di halaman login (`/login`).

---

## 🎬 Panduan Walkthrough Demo (5–7 Menit)

1. **Login Skenario Utama:** Buka `/login`, pilih persona **Chacha Coffee**.
2. **Analisis Baseline (`/overview`):** Periksa Growth Score (**64/100**) dan identifikasi bottleneck aktif: **Customer Acquisition**.
3. **AI Reasoning (`/growth/assistant`):** Tanyakan masalah bisnis dan klik *"Why am I seeing this?"* untuk melihat transparansi kalkulasi.
4. **Pusat Solusi (`/solutions`):** Telusuri rekomendasi solusi terarah Telkomsel (**MyAds** dan **Kuncie**).
5. **Aktivasi MyAds (`/solutions/activation`):** Lakukan tahapan verifikasi identitas, persetujuan, dan top up saldo kampanye.
6. **Eksekusi Kampanye (`/demo`):** Buka panel demo di navigasi atas, klik **"Simulate Campaign Run"** untuk menghasilkan 24.500 jangkauan dan konversi riil.
7. **Selesaikan Misi (`/missions`):** Centang misi mingguan yang telah diselesaikan.
8. **Tutup Siklus 30 Hari (`/demo`):** Klik **"Close 30-Day Growth Cycle"**.
9. **Hasil Before vs After (`/growth/before-after`):** Buktikan pendapatan meningkat dari Rp20.000.000 menjadi Rp24.200.000 (+21%) dan konversi naik dari 3.0% menjadi 4.1%.
10. **Evaluasi Skor Baru (`/growth/review`):** Skor tumbuh dari **64 $\to$ 74**, dan sistem otomatis mendeteksi bottleneck berikutnya (**Customer Retention**), menghasilkan rencana aksi 30 hari yang baru!

---

## 🧪 Pengujian & Verifikasi (Testing)

Proyek ini dilengkapi dengan rangkaian pengujian komprehensif (*automated feature test suite*):

```bash
php artisan test
```

```
PASS  Tests\Feature\AuthTest
PASS  Tests\Feature\FinanceInventoryTest
PASS  Tests\Feature\GrowthLoopTest
PASS  Tests\Feature\MissionTest
PASS  Tests\Feature\OnboardingDiagnosticTest
PASS  Tests\Feature\ScoringTest
PASS  Tests\Feature\TierAccessTest

Tests:    29 passed (228 assertions)
Duration: 3.06s
```

Pengujian memvalidasi:
- Otentikasi, hashing password, dan verifikasi OTP kedaluwarsa.
- Deterministik skor diagnostik dan bobot 4 dimensi.
- Logika pemilihan bottleneck dan rekomendasi berbasis aturan.
- Proteksi otorisasi berjenjang (HTTP 403 Forbidden pada akses fitur tidak sah).
- Keutuhan data Growth Finance (P&L, OCR struk) dan Growth Inventory.
- Validasi loop tertutup (kenaikan skor Chacha Coffee 64 $\to$ 74 tanpa *score drift*).

---

## 📚 Dokumentasi Lengkap

Dokumentasi teknis mendalam tersedia di direktori [`docs/`](docs/):

- 🏛️ **[Arsitektur Teknis (`docs/ARCHITECTURE.md`)](docs/ARCHITECTURE.md)**: Desain monolit, pola repositori/layanan, dan struktur data.
- 🔄 **[Framework Growth Loop (`docs/GROWTH_LOOP.md`)](docs/GROWTH_LOOP.md)**: Rincian matematis 17 tahapan, bobot dimensi, dan deteksi kendala.
- 📡 **[Integrasi Telkomsel (`docs/TELKOMSEL_INTEGRATIONS.md`)](docs/TELKOMSEL_INTEGRATIONS.md)**: Penjelasan rinci 9 adapter ekosistem dan 2 kapabilitas native.
- 🎯 **[Skenario Demo Juri (`docs/DEMO_WALKTHROUGH.md`)](docs/DEMO_WALKTHROUGH.md)**: Panduan presentasi step-by-step untuk kompetisi.
- 🗺️ **[Peta Rute & Akses Tier (`docs/API_ROUTES.md`)](docs/API_ROUTES.md)**: Matriks rute web dan kebijakan middleware.

---

## 🎨 Tipografi & Desain Brand

- **Headline & Metrik:** Telkomsel Batik Sans Bold (`font-display`).
- **Body & Tampilan UI:** Neue Montreal (`font-sans`).
- **Desain UI:** Merujuk pada spesifikasi ekspor Google Stitch di `design/stitch/` (`nusantara_enterprise_growth_os/DESIGN.md`).
- **Kustomisasi Logo:** Letakkan file logo di `public/brand/tumbuh-logo.svg` dan atur env `TUMBUH_LOGO=brand/tumbuh-logo.svg`.

---

## 👥 Tim Pengembang

Dikembangkan untuk **Telkomsel Business Case Competition**:
- **Informatics** — UPN Veteran Jawa Timur
- **Information Systems** — Universitas Brawijaya
- **International Business Management** — Universitas Ciputra Surabaya
- **Public Relations** — BINUS @ Malang
- **Visual Communication Design** — Institut Teknologi Sepuluh Nopember (ITS)

---

<p align="center">
  Dibuat dengan ❤️ untuk UMKM Indonesia Menuju Masa Depan Digital Berkelanjutan.
</p>
