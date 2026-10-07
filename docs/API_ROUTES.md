# TUMBUH — Route Map & Tier Gating Matrix

This document maps all web routes, view handlers, and tier access policies across the TUMBUH platform.

---

## 1. Route Matrix & Middleware Protection

All authenticated routes require `auth`, `verified.phone`, and `onboarded`. High-tier features are safeguarded by the `tier:` route middleware.

| HTTP Method | URI Pattern | Name | Tier Required | Purpose |
|---|---|---|---|---|
| **GET** | `/` | `public.landing` | Public | Public landing page with hero video |
| **GET** | `/how-it-works` | `public.how-it-works` | Public | Interactive Growth Loop capability diagram |
| **GET** | `/login` | `login` | Public | User authentication screen + demo switcher |
| **POST** | `/login` | `login.submit` | Public | Login verification handler |
| **POST** | `/logout` | `logout` | Auth | Session termination |
| **GET** | `/register` | `register` | Public | Account registration form |
| **POST** | `/register` | `register.submit` | Public | User registration handler |
| **GET** | `/verify` | `verify` | Auth | 6-digit phone OTP verification screen |
| **POST** | `/verify` | `verify.submit` | Auth | OTP validation and timestamp verification |
| **GET** | `/onboarding/{step}` | `onboarding.step` | Auth | 6-step business diagnostic onboarding |
| **POST** | `/onboarding/{step}` | `onboarding.save` | Auth | Step persistence and progress increment |
| **GET** | `/goals` | `goals` | Auth | Target 90-day goal selection |
| **POST** | `/goals` | `goals.save` | Auth | Active business goal registration |
| **GET** | `/diagnostic` | `diagnostic.index` | Auth | Growth Diagnostic single-question flow |
| **POST** | `/diagnostic/answer`| `diagnostic.answer`| Auth | Question autosave handler |
| **GET** | `/diagnostic/review`| `diagnostic.review`| Auth | Diagnostic answer review before scoring |
| **POST** | `/diagnostic/submit`| `diagnostic.submit`| Auth | Triggers GrowthScoreService calculation |
| **GET** | `/overview` | `overview` | Bronze | Executive dashboard (Score, KPIs, Bottleneck) |
| **GET** | `/growth/score` | `growth.score` | Bronze | 4-dimension diagnostic score drill-down |
| **GET** | `/growth/assistant`| `growth.assistant`| Bronze | Deterministic AI Assistant & reasoning |
| **GET** | `/growth/ledger` | `growth.ledger` | **Silver** | Historical KPI timeline |
| **GET** | `/growth/before-after` | `growth.before-after` | **Silver** | 30-Day Before vs After metric comparison |
| **GET** | `/growth/review` | `growth.review` | **Silver** | Cycle-end AI Performance Review |
| **GET** | `/plan` | `plan.index` | Bronze | 30-Day Action Plan view |
| **GET** | `/missions` | `missions.index` | Bronze | Weekly missions checklist |
| **POST** | `/missions/{id}/check` | `missions.check` | Bronze | Mission completion toggle |
| **GET** | `/solutions` | `solutions.index` | Bronze | Telkomsel solution catalogue matching |
| **GET** | `/solutions/{key}` | `solutions.show` | Bronze | Solution detail view |
| **GET** | `/solutions/activation` | `solutions.activation` | **Silver** | Solution onboarding & KYC state machine |
| **GET** | `/campaigns` | `campaigns.index` | **Silver** | MyAds marketing campaigns list |
| **POST** | `/campaigns` | `campaigns.store` | **Silver** | Create and schedule MyAds campaign |
| **GET** | `/operate` | `operate.index` | Bronze | Business Operations central hub |
| **GET** | `/finance` | `finance.index` | Bronze | Growth Finance cash book ledger |
| **POST** | `/finance/entry` | `finance.store` | Bronze | Record Income or Expense transaction |
| **GET** | `/finance/scan` | `finance.scan` | **Silver** | Receipt scanner (OCR simulation) |
| **GET** | `/finance/report` | `finance.report` | **Silver** | P&L Statement & Cash Flow analytics |
| **GET** | `/inventory` | `inventory.index` | Bronze | Growth Inventory stock listing |
| **POST** | `/inventory/product`| `inventory.store`| Bronze | Add SKU / product catalog item |
| **POST** | `/inventory/movement`| `inventory.movement`| Bronze | Restock or adjust inventory levels |
| **GET** | `/rewards` | `rewards.index` | Bronze | Growth Passport & Reward Center |
| **GET** | `/rewards/tiers` | `rewards.tiers` | Bronze | Tier comparison and upgrade requirements |
| **GET** | `/analytics` | `analytics.index` | Bronze | Comprehensive business metrics dashboard |
| **GET** | `/scale/readiness` | `scale.readiness` | **Silver** | Expansion readiness evaluation score |
| **GET** | `/scale/msight` | `scale.msight` | **Gold** | Telkomsel MSIGHT mobility analytics |
| **GET** | `/scale/sitesense` | `scale.sitesense` | **Gold** | SiteSense geospatial store feasibility |
| **GET** | `/scale/dce` | `scale.dce` | **Silver** | Digital Creative Entrepreneurs pathway |
| **GET** | `/profile` | `profile.index` | Bronze | Business profile & data privacy consent |
| **GET** | `/notifications` | `notifications.index` | Bronze | In-app notification center |
| **GET** | `/demo` | `demo.index` | Bronze | Demo Panel for scenario simulation |
| **POST** | `/demo/reset/{id}` | `demo.reset` | Bronze | Reset individual demo scenario |
| **POST** | `/demo/simulate-campaign` | `demo.simulate-campaign` | Bronze | Trigger campaign delivery simulation |
| **POST** | `/demo/close-cycle` | `demo.close-cycle` | Bronze | Simulate 30-day growth cycle completion |

---

## 2. Server-Side Tier Enforcement

The `RequireTier` middleware intercepts all incoming requests configured with `tier:{feature}`:
```php
public function handle(Request $request, Closure $next, string $feature): Response
{
    $business = $request->user()?->business;
    
    if (! $this->tierService->allows($business, $feature)) {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Tier upgrade required'], 403);
        }
        return response()->view('app.tier-gate', [
            'requiredTier' => $this->tierService->requiredTierFor($feature),
            'featureKey' => $feature,
        ], 403);
    }
    
    return $next($request);
}
```
This guarantees that Silver and Gold tier features cannot be bypassed via URL manipulation.
