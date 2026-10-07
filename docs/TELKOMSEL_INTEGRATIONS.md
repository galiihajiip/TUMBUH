# TUMBUH — Telkomsel Ecosystem Integrations

This document outlines how TUMBUH connects to and orchestrates Telkomsel's enterprise and digital business capabilities.

---

## 1. Integration Philosophy

In the TUMBUH prototype, external integrations are organized behind the **Adapter Pattern** (`app/Services/Telkomsel/`). 

> ⚠️ **Demo Environment Policy:** All external Telkomsel integrations run as high-fidelity local simulations. No live credentials or Telkomsel production customer data are accessed. The UI transparently denotes simulated data with the badge `[Simulated / Prototype Integration]`.

All adapters implement `App\Services\Telkomsel\TelkomselAdapter` and are registered in `App\Services\Telkomsel\AdapterRegistry`.

---

## 2. Capability Catalog & Adapter Matrix

| Ecosystem Capability | Adapter Class | Target Problem / Bottleneck | Simulated Behavior |
|---|---|---|---|
| **MyAds** | `MyAdsAdapter` | Customer Acquisition | Identity verification, top-up balance, targeted SMS broadcast creation, click-through & lead tracking |
| **Kuncie** | `KuncieAdapter` | Competency & Knowledge Deficits | Contextual business masterclasses, micro-learning modules linked directly to active bottleneck |
| **UMKM Plus** | `UmkmPlusAdapter` | Digital Infrastructure & Connectivity | Dedicated business internet tiers, quota eligibility, MyTelkomsel bundle activation |
| **DigiPOS** | `DigiPosAdapter` | Cash Flow & Distribution | Distribution network transactions, digital wholesale stock purchasing, micro-payment routing |
| **Soundbox** | `SoundboxAdapter` | Payment Verification & Operations | Real-time QRIS payment voice announcement verification, payment gateway settlement |
| **DigiAds** | `DigiAdsAdapter` | Advanced Multi-channel Advertising | Consultative enterprise advertising, programmatic ads, rich media display networks |
| **RCS** | `RcsAdapter` | Customer Retention & Engagement | Rich Communication Services, interactive two-way business messaging, verified sender branding |
| **MSIGHT** | `MSightAdapter` | Market Expansion & Footfall Research | Telkomsel big data mobility analytics, demographic density, visitor dwell time analysis |
| **SiteSense** | `SiteSenseAdapter` | Store Location & Geospatial Feasibility | Geospatial site scoring, competitor proximity, purchasing power index per sub-district |
| **DCE** | `DceAdapter` | High-Growth Acceleration | Digital Creative Entrepreneurs accelerator eligibility scoring, mentorship intake pipeline |

---

## 3. Native Capabilities (Beyond Existing Telkomsel Services)

To achieve a true closed loop, TUMBUH introduces two **native operational modules** that bridge Telkomsel's network to internal business metrics:

### 3.1. Growth Finance (`app/Services/FinanceService.php`)
- **P&L Statement:** Real-time Gross Margin, Operating Expenses, Net Margin calculations.
- **Cash Flow Ledger:** Income vs Expense categorization with payment method classification (Cash, QRIS, Transfer).
- **Simulated Receipt OCR:** Deterministic parser converting receipt snapshots into structured expense entries.

### 3.2. Growth Inventory (`app/Services/InventoryService.php`)
- **Stock Tracking:** Real-time inventory valuations (COGS and Retail Value).
- **Sales Linkage:** Automatic stock reduction whenever sales transactions are logged.
- **Stockout Alerts:** Automated safety stock thresholds triggering restock missions.

---

## 4. Production Integration Roadmap

To connect TUMBUH to Telkomsel's live production infrastructure:
1. **Telkomsel Open Gateway (APIs):** Implement OAuth 2.0 client credentials in `TelkomselAdapter`.
2. **MyAds REST API:** Replace `MyAdsAdapter::activate()` with endpoints `POST /v1/campaigns` and webhooks for campaign delivery stats.
3. **Soundbox Webhooks:** Expose signed webhook endpoints for instant QRIS transaction ingestion.
4. **MSIGHT & SiteSense APIs:** Query authenticated Telkomsel Big Data data lakes using authorized polygon GeoJSON requests.
5. **No Domain Refactoring:** Because controllers call domain services, which call `AdapterRegistry`, **zero changes to business logic or UI views are necessary** when upgrading from mocks to live APIs.
