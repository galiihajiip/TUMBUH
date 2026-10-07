# TUMBUH — Technical Architecture

This document details the software architecture, design patterns, and system components of **TUMBUH** (*Telkomsel untuk UMKM Tumbuh*).

---

## 1. High-Level Architectural Pattern

TUMBUH is intentionally engineered as an **opinionated Laravel 13 monolith**, adhering to high cohesiveness and low operational overhead. It deliberately avoids distributed microservices, separate Single Page Applications (SPAs), message broker queues, or external cache clusters to ensure zero friction during local evaluation and prototype demonstrations.

```
┌────────────────────────────────────────────────────────────────────────┐
│                        Client Presentation Layer                       │
│    Blade Templates + Tailwind CSS v4 + Alpine.js (Reactive UI)        │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │ HTTP Requests / Inertia-less HTML
┌───────────────────────────────────▼────────────────────────────────────┐
│                       HTTP & Routing Layer                             │
│   Web Routes (`routes/web.php`) + Route Middleware (`tier:`, auth)    │
│   Controllers (`app/Http/Controllers/*`)                                │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │ Invokes Domain Logic
┌───────────────────────────────────▼────────────────────────────────────┐
│                        Domain Service Layer                            │
│  `app/Services/*` (Formulas, Orchestration, Analytics, State Machine)  │
└───────────────────┬────────────────────────────────┬───────────────────┘
                    │                                │
┌───────────────────▼───────────┐      ┌─────────────▼───────────────────┐
│     Telkomsel Adapter Layer   │      │       Eloquent Persistence      │
│  `app/Services/Telkomsel/*`   │      │        `app/Models/*`           │
│   Mock Integration Adapters   │      │        SQLite Database          │
└───────────────────────────────┘      └─────────────────────────────────┘
```

---

## 2. Directory Structure & Layer Responsibilities

```
tumbuh/
├── app/
│   ├── Http/
│   │   ├── Controllers/         # Thin HTTP request handlers; NO formulas
│   │   └── Middleware/          # Gating: TierMiddleware, EnsureOnboarded, EnsureVerified
│   ├── Models/                  # Eloquent database entities with relationships
│   ├── Providers/               # Service container bindings
│   ├── Services/                # Pure business logic and domain engines
│   │   ├── Demo/                # Scenario generators for reproducible states
│   │   └── Telkomsel/           # Mock integration adapters (Adapter pattern)
│   └── helpers.php              # Global helper functions (formatting, icons)
├── config/
│   └── tumbuh.php               # Feature tiers, question catalog, system constants
├── database/
│   ├── migrations/              # SQLite database schema definitions
│   └── seeders/                 # Deterministic question banks and demo accounts
├── design/                      # Visual reference exports (Google Stitch)
├── docker/                      # Container configuration and entrypoint
├── docs/                        # Specifications, walkthroughs, and architecture docs
├── resources/
│   ├── css/app.css              # Tailwind v4 configuration and custom utilities
│   ├── fonts/                   # Bundled local brand fonts (Telkomsel Batik Sans, Neue Montreal)
│   ├── js/app.js                # Alpine.js initialization
│   └── views/                   # Server-rendered Blade views and atomic components
└── routes/
    └── web.php                  # Web route definitions with middleware guards
```

---

## 3. Key Design Patterns

### 3.1. Thin Controllers, Rich Domain Services
Controllers must remain strictly responsible for HTTP concerns: input validation, calling the relevant Domain Service, and returning an HTTP response or Blade view. **No formulas, math calculations, or orchestration state transitions are permitted in Controllers or Blade templates.**

All formulas reside in `app/Services/`:
- `GrowthScoreService`: 4-dimension scoring matrix weighted algorithm.
- `BottleneckService`: Deterministic constraint identification.
- `RecommendationService`: Rule-based matching engine between bottlenecks and Telkomsel solutions.
- `ActionPlanService`: 30-day mission generation and milestone tracking.
- `KpiService`: Growth Ledger delta tracking (Before vs After comparisons).
- `FinanceService` & `FinancialAnalyticsService`: P&L, COGS, Cash Flow, and receipt OCR simulation.
- `InventoryService` & `InventoryAnalyticsService`: Stock movements, sales linkage, stockout alerts.
- `ExpansionReadinessService`: Multi-factor scale scoring and SiteSense/MSIGHT rankings.

### 3.2. Adapter Pattern for Telkomsel Integrations
All Telkomsel ecosystem solutions are encapsulated behind a unified contract: `app/Services/Telkomsel/TelkomselAdapter.php`.
```php
interface TelkomselAdapter
{
    public function getKey(): string;
    public function getName(): string;
    public function getCategory(): string;
    public function getCapabilities(): array;
    public function getStatus(Business $business): array;
    public function activate(Business $business, array $params = []): array;
}
```
- **Local Isolation:** The prototype runs completely offline with simulated deterministic data.
- **Production Readiness:** Swapping mocks with actual production APIs (Telkomsel Open Gateway / REST) requires only updating the respective Adapter class in `app/Services/Telkomsel/` without touching domain services.

### 3.3. Tier-Based Authorization (Server-Side Enforcement)
Features are gated according to UMKM growth tiers (`Bronze`, `Silver`, `Gold`).
- Enforced at route level: `Route::middleware(['auth', 'tier:campaigns'])`.
- Defined declaratively in `config/tumbuh.php['features']`.
- Evaluated centrally via `TierService::allows($business, $featureKey)`.
- Prevents UI tampering: HTTP 403 response with an actionable upgrade prompt is returned if unauthorized.

---

## 4. Database Strategy & Persistence

- **Engine:** SQLite 3 with foreign key enforcement enabled.
- **Data Volume Persistence:** In Docker environments, SQLite data is mounted into persistent Docker volumes (`tumbuh-data`), surviving container restarts.
- **Deterministic Seeding:** The seeders (`DatabaseSeeder`, `DemoSeeder`, `PeerSeeder`, `QuestionSeeder`) produce identical datasets, scores, and peer cohorts (28 businesses across Indonesia) every run.

---

## 5. Frontend & Asset Pipeline

- **CSS Engine:** Tailwind CSS v4 using modern `@theme` and `@utility` directives.
- **Typography:** Self-hosted zero-external-dependency web fonts:
  - *Telkomsel Batik Sans Bold* (`font-display`): Used for brand wordmarks, headlines, and primary metric numbers.
  - *Neue Montreal* (`font-sans`): Used for UI chrome, body typography, labels, and tables.
- **Interactivity:** Alpine.js for lightweight client interactions (modals, dropdowns, autosaving diagnostic questionnaires, tabs).
