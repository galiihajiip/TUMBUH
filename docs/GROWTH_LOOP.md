# TUMBUH — The Growth Loop Framework

> **"Growth Loop" is the core operating mechanism inside TUMBUH, turning digital adoption into measurable business growth.**

---

## 1. Executive Summary

Most UMKM digitization initiatives fail because they treat digital tools as isolated destinations. A business owner downloads a POS app, attends a training session, or sets up social ads, but lacks a closed feedback loop connecting actions to business outcomes.

**TUMBUH solves this with the Growth Loop:** a continuous 17-step operational flywheel that diagnoses business health, recommends targeted Telkomsel capabilities, tracks execution, and quantifies before-and-after impact.

---

## 2. The 17-Step Growth Loop Lifecycle

```
[1] DISCOVER ───────▶ [2] SIGN UP ───────▶ [3] ONBOARD ──────▶ [4] GOAL SETTING
                                                                      │
┌─────────────────────────────────────────────────────────────────────┘
▼
[5] DIAGNOSE ───────▶ [6] SCORE ─────────▶ [7] BOTTLENECK ───▶ [8] AI RECOMMENDATION
                                                                      │
┌─────────────────────────────────────────────────────────────────────┘
▼
[9] ACTION PLAN ────▶ [10] ACTIVATE ─────▶ [11] EXECUTE ─────▶ [12] MEASURE
                                                                      │
┌─────────────────────────────────────────────────────────────────────┘
▼
[13] REVIEW ────────▶ [14] IMPROVE ──────▶ [15] GROW ────────▶ [16] SCALE
  ▲                                                                   │
  └───────────────────────── [17] REDIAGNOSE ↺ ───────────────────────┘
```

### Detailed Stage Breakdown

| # | Stage | User Action | System Engine | Output |
|---|---|---|---|---|
| **1** | **Discover** | Visits landing page | `PublicController` | Value proposition & interactive loop diagram |
| **2** | **Sign Up** | Submits credentials & phone | `RegisterController` / `OtpController` | Verified user session via OTP |
| **3** | **Onboard** | Fills 6-step business profile | `OnboardingController` | Business metadata (industry, location, revenue) |
| **4** | **Goal Setting** | Selects target 90-day objective | `OnboardingController` | Active business goal (e.g., Increase Repeat Rate) |
| **5** | **Diagnose** | Answers diagnostic questions | `DiagnosticService` | Category-specific questionnaire completion |
| **6** | **Score** | Views Growth Score (0–100) | `GrowthScoreService` | 4-dimension score breakdown & benchmark |
| **7** | **Bottleneck** | Identifies primary operational blocker | `BottleneckService` | Critical constraint detected (e.g., Acquisition) |
| **8** | **AI Recommendation** | Views tailored Telkomsel solution | `RecommendationService` | Matched solution with *Why am I seeing this?* logic |
| **9** | **Action Plan** | Receives structured 30-day plan | `ActionPlanService` | 4 weekly missions with measurable KPIs |
| **10** | **Activate** | Initiates solution onboarding | `SolutionService` + Adapter | Verified identity, account top-up, ready state |
| **11** | **Execute** | Executes missions & runs campaigns | `MissionController`, `CampaignService` | Campaign broadcast, marketing assets launched |
| **12** | **Measure** | Logs daily income, costs & sales | `FinanceService`, `InventoryService` | Live KPI tracking & ledger updates |
| **13** | **Review** | Completes 30-day cycle review | `PerformanceReviewService` | Closed-loop before vs after report |
| **14** | **Improve** | Receives AI debrief on score changes | `PerformanceReviewService` | Score updates (e.g., +10 pts) and next focus |
| **15** | **Grow** | Unlocks Passport badges & perks | `PassportService`, `RewardService` | Progression from *Productive* to *Growing* |
| **16** | **Scale** | Evaluates branch expansion & DCE | `ExpansionReadinessService` | SiteSense & MSIGHT geo-scoring, DCE pathway |
| **17** | **Rediagnose** | Starts next iteration cycle | `DiagnosticService` | New diagnostic cycle for next bottleneck |

---

## 3. Growth Score Formula & Dimension Weights

The overall **Growth Score** ($S \in [0, 100]$) is calculated as a weighted average across four core business dimensions:

$$S = (W_{\text{acq}} \times S_{\text{acq}}) + (W_{\text{ops}} \times S_{\text{ops}}) + (W_{\text{ret}} \times S_{\text{ret}}) + (W_{\text{scale}} \times S_{\text{scale}})$$

### Dimension Weights:
1. **Customer Acquisition ($S_{\text{acq}}$)** — Weight: **30%**
   - Marketing channel diversity, ad efficiency, lead conversion, discovery presence.
2. **Operational Efficiency ($S_{\text{ops}}$)** — Weight: **25%**
   - Cash flow recording, inventory tracking accuracy, margin stability, digital payment adoption.
3. **Customer Retention ($S_{\text{ret}}$)** — Weight: **25%**
   - Repeat purchase rate, CRM usage, customer communication channels, loyalty programs.
4. **Scale & Market Readiness ($S_{\text{scale}}$)** — Weight: **20%**
   - Revenue trajectory, financial auditability, multi-channel capability, expansion capital.

---

## 4. Deterministic Bottleneck Detection

The bottleneck is identified deterministically using the following hierarchy:
1. The dimension with the lowest normalized score below the minimum benchmark ($< 60$).
2. If multiple dimensions are tied, priority ordering: **Acquisition $\to$ Operations $\to$ Retention $\to$ Scale**.
3. Specific red flags trigger direct bottleneck overrides:
   - High margin erosion ($> 15\%$ quarter-over-quarter) forces **Operations / Margin** bottleneck.
   - Low repeat purchase ($< 15\%$) with adequate traffic forces **Retention** bottleneck.

---

## 5. Closed-Loop Validation (The Chacha Coffee Cycle)

The closed loop is mathematically proven through the demo scenario:
- **Baseline:** Score = **64**, Bottleneck = **Customer Acquisition**.
- **Action:** Activate MyAds + Kuncie, launch localized SMS targeting campaign.
- **Result:**
  - Revenue increases from **Rp20.000.000** to **Rp24.200.000** (+21%).
  - Conversion rate rises from **3.0%** to **4.1%** (+1.1 percentage points).
  - Repeat customer rate remains flat at **21%**.
- **After Cycle Close:**
  - Growth Score increases from **64 $\to$ 74** (+10 points).
  - The Acquisition dimension improves significantly.
  - The *new* lowest dimension becomes **Customer Retention**, automatically triggering the next 30-Day Retention Action Plan without score drift or hallucinations.
