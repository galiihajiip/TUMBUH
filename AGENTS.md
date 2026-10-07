# TUMBUH — project notes

- Product name is **TUMBUH** (Telkomsel untuk UMKM Tumbuh). "Growth Loop" is the mechanism, never the product name.
- Laravel 13 monolith · Blade + Tailwind v4 (`@utility` in `resources/css/app.css`, not `@layer components` for @apply-able classes) + Alpine · SQLite.
- Business logic lives in `app/Services`; formulas must not go into controllers or Blade.
- Telkomsel integrations are mocks behind `app/Services/Telkomsel/TelkomselAdapter`. Never label simulated data as live.
- Tier access: `config/tumbuh.php` `features` + `TierService` (read the map directly — keys contain dots) + `tier:` middleware.
- Demo scenarios: `app/Services/Demo/DemoScenarioBuilder` (used by the seeder and Demo Panel reset).
- Visual reference: `design/stitch/` (Stitch export). Feature brief: `docs/`.
- Verify with `php artisan test` and `php artisan migrate:fresh --seed`.
- Fonts: Telkomsel Batik Sans = headlines/metrics (`font-display`), Neue Montreal = body/UI (`font-sans`); files in `resources/fonts/`.
