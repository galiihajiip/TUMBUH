# TUMBUH — Competition Demo Walkthrough

This guide provides a structured, 5 to 7 minute demonstration script designed for competition judges, partners, and stakeholders.

---

## 1. Demo Credentials & Accounts

All accounts use the universal password: **`tumbuh123`**
Demo OTP for verification: **`123456`**

| Account | Business | Tier | Sector & City | Demonstrated Journey |
|---|---|---|---|---|
| `aisha@tumbuh.demo` | **Aisha Dessert House** | **Bronze** | F&B, Malang | Early-stage UMKM. Score 34. Acquisition bottleneck, manual processes, unintegrated digital presence. |
| `chacha@tumbuh.demo` | **Chacha Coffee** | **Silver** | F&B, Surabaya | **Primary Closed-Loop Showcase:** Baseline score 64, acquisition bottleneck, MyAds campaign execution, cycle close, and progression to score 74. |
| `sari@tumbuh.demo` | **Sari Bakery** | **Silver** | Bakery, Surabaya | Operational challenge: Strong sales, but severe margin erosion (54% $\to$ 46%) and inventory discrepancies. |
| `bima@tumbuh.demo` | **Nusantara Fashion** | **Gold** | Fashion, Surabaya | Scale stage: Score 88. Multi-branch readiness, SiteSense geospatial site selection, MSIGHT density analytics, DCE accelerator pathway. |

> 💡 **Quick Login:** On the `/login` screen, click the **Demo Accounts** dropdown to instantly populate any scenario.

---

## 2. Recommended Walkthrough Flow (5–7 Minutes)

### Step 1: The Problem & Baseline Diagnosis (1.5 min)
1. Navigate to `/login` and select **Chacha Coffee**.
2. Arrive at **Overview (`/overview`)**:
   - Highlight the **Growth Score: 64 / 100** and the detected bottleneck: **Customer Acquisition**.
   - Show how the score combines data from multiple dimensions (Acquisition: 52, Operations: 72, Retention: 65, Scale: 60).
3. Open the **AI Assistant (`/growth/assistant`)**:
   - Demonstrate the 4 deterministic prompts:
     - *What's wrong with my business?*
     - *Why is Customer Acquisition my bottleneck?*
     - *What actions should I take?*
     - *How will I know if it worked?*
   - Click **"Why am I seeing this?"** to reveal the transparent, auditable business logic.

---

### Step 2: Solution Matching & Activation (1.5 min)
1. Go to **Solutions (`/solutions`)**:
   - Point out that solutions are sorted by bottleneck relevance: **Telkomsel MyAds** and **Kuncie** are ranked #1 and #2.
2. Click **MyAds** to enter the **Activation Center (`/solutions/activation`)**:
   - Walk through the 3-step activation pipeline:
     1. Account & Identity Verification (`NIK`, Business License).
     2. Telkomsel Verification & Approval.
     3. Wallet Top-Up.
3. Open **Campaigns (`/campaigns`)**:
   - Review the targeted SMS campaign targeting customers within a 3km radius of Gubeng, Surabaya.

---

### Step 3: Simulation & Execution (1 min)
1. Open the floating **Demo Panel (`/demo`)** in the top navigation.
2. Under the active session, click **"Simulate Campaign Run"**:
   - Watch the campaign transition from *Scheduled* to *Active*.
   - View simulated results: 24,500 target reach, 612 clicks (2.5% CTR), and 82 direct conversions.
3. Navigate to **Missions (`/missions`)**:
   - Check off Weekly Mission 1: *"Launch targeted SMS ad for lunch promo"*.
   - Check off Weekly Mission 2: *"Review campaign conversion data"*.

---

### Step 4: Measuring the Closed Loop (1.5 min)
1. In the **Demo Panel**, click **"Close 30-Day Growth Cycle"**.
2. Redirect to **Growth Ledger Before vs After (`/growth/before-after`)**:
   - **Monthly Revenue:** Rp20.000.000 $\to$ **Rp24.200.000 (+21%)**
   - **Lead Conversion:** 3.0% $\to$ **4.1% (+1.1 pp)**
   - **Repeat Purchase:** 22% $\to$ **21% (-1.0 pp)** (Highlight this for the next step!)
3. Navigate to **AI Performance Review (`/growth/review`)**:
   - Note the AI summary: *"Akuisisi membaik signifikan. Namun, repeat customer stagnan. Bottleneck berikutnya adalah Customer Retention."*
   - Verify the score progression: **64 $\to$ 74 (+10 points)**.
4. Return to **Missions (`/missions/plan`)**:
   - Notice the system automatically generated the next cycle: **30-Day Customer Retention Plan** with Kuncie modules and RCS messaging!
   - The loop is closed and starts over: **DISCOVER $\to$ SCALE $\to$ REDIAGNOSE ↺**.

---

### Step 5: Advanced Modules Showcase (Optional, 1–2 min)

#### Option A: Growth Finance & Inventory (Sari Bakery)
- Switch to **Sari Bakery** (`sari@tumbuh.demo`).
- Go to `/finance/report`: Highlight P&L, COGS breakdown, and margin erosion alert.
- Go to `/finance/scan`: Demonstrate receipt scanner OCR simulation with instant expense logging.
- Go to `/inventory`: Show stock valuations and the *flour & butter* low-stock alert.

#### Option B: Scale & Location Intelligence (Nusantara Fashion)
- Switch to **Nusantara Fashion** (`bima@tumbuh.demo`).
- Go to `/scale/readiness`: Score 88, 100% expansion readiness.
- Go to `/scale/sitesense`: Location ranking of candidate branches in Surabaya (Gubeng score: 92 vs Rungkut score: 78).
- Go to `/scale/msight`: Telkomsel footfall mobility density maps.
- Go to `/scale/dce`: Priority pathway eligibility for Digital Creative Entrepreneurs.

---

## 3. Resetting Demo State

- **Per Account:** Open `/demo` and click the ↺ Reset icon beside any account. Only that account's state is refreshed.
- **Complete Factory Reset:** Run `php artisan migrate:fresh --seed` in the terminal or inside Docker container (`docker compose exec app php artisan migrate:fresh --seed`).
