<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Guided Business Onboarding (6 steps) and Business Goal setting.
 * Each step persists immediately, so users can resume where they left off.
 */
class OnboardingController extends Controller
{
    public const STEPS = [
        1 => ['title' => 'Identitas Usaha', 'en' => 'Business Identity', 'icon' => 'storefront'],
        2 => ['title' => 'Lokasi Usaha', 'en' => 'Location', 'icon' => 'location_on'],
        3 => ['title' => 'Skala Usaha', 'en' => 'Business Scale', 'icon' => 'monitoring'],
        4 => ['title' => 'Saluran Penjualan', 'en' => 'Sales Channels', 'icon' => 'shopping_bag'],
        5 => ['title' => 'Alat Digital', 'en' => 'Digital Tools', 'icon' => 'devices'],
        6 => ['title' => 'Ekosistem Telkomsel', 'en' => 'Telkomsel Ecosystem', 'icon' => 'hub'],
    ];

    private function draft(Request $request): Business
    {
        return $request->user()->business()->first() ?? $request->user()->business()->create(['name' => '', 'onboarding_step' => 1]);
    }

    public function show(Request $request, int $step = 0)
    {
        $business = $this->draft($request);
        if ($business->onboardingComplete() && $step === 0) {
            return redirect()->route($business->goals_set_at ? 'overview' : 'goals.edit');
        }

        $step = $step ?: $business->onboarding_step;
        // Steps cannot be skipped ahead of the first incomplete one.
        $step = max(1, min($step, $business->onboarding_step, 6));

        return view('onboarding.step', ['business' => $business, 'step' => $step, 'steps' => self::STEPS]);
    }

    public function store(Request $request, int $step)
    {
        $business = $this->draft($request);
        abort_if($step < 1 || $step > 6 || $step > $business->onboarding_step, 404);

        $data = match ($step) {
            1 => $request->validate([
                'name' => ['required', 'string', 'max:120'],
                'category' => ['required', Rule::in(array_keys(config('tumbuh.categories')))],
                'sub_category' => ['nullable', 'string', 'max:80'],
                'description' => ['nullable', 'string', 'max:500'],
                'start_year' => ['required', 'integer', 'min:1950', 'max:'.now()->year],
                'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            ]),
            2 => $request->validate([
                'province' => ['required', Rule::in(array_keys(config('tumbuh.provinces')))],
                'city' => ['required', 'string', 'max:80'],
                'district' => ['required', 'string', 'max:80'],
                'address' => ['nullable', 'string', 'max:255'],
                'has_physical_store' => ['required', 'boolean'],
                'outlets' => ['required', 'integer', 'min:0', 'max:500'],
            ]),
            3 => $request->validate([
                'employees' => ['required', 'integer', 'min:0', 'max:10000'],
                'revenue_range' => ['required', Rule::in(array_keys(config('tumbuh.revenue_ranges')))],
                'avg_monthly_transactions' => ['required', 'integer', 'min:0', 'max:1000000'],
                'avg_order_value' => ['required', 'integer', 'min:0', 'max:100000000'],
                'monthly_customers' => ['required', 'integer', 'min:0', 'max:1000000'],
            ]),
            4 => $request->validate([
                'sales_channels' => ['required', 'array', 'min:1'],
                'sales_channels.*' => [Rule::in(array_keys(config('tumbuh.sales_channels')))],
                'instagram_url' => [Rule::requiredIf(in_array('instagram', (array) $request->input('sales_channels'), true)), 'nullable', 'url', 'max:255'],
                'marketplace_url' => [Rule::requiredIf(in_array('marketplace', (array) $request->input('sales_channels'), true)), 'nullable', 'url', 'max:255'],
            ], ['sales_channels.required' => 'Pilih minimal satu saluran penjualan.']),
            5 => $request->validate([
                'digital_tools' => ['nullable', 'array'],
                'digital_tools.*' => [Rule::in(array_keys(config('tumbuh.digital_tools')))],
            ]),
            6 => $request->validate([
                'telkomsel_products' => ['required', 'array', 'min:1'],
                'telkomsel_products.*' => [Rule::in(array_keys(config('tumbuh.telkomsel_products')))],
            ], ['telkomsel_products.required' => 'Pilih minimal satu opsi (atau "Belum menggunakan").']),
        };

        if ($step === 1) {
            if ($request->hasFile('logo')) {
                $data['logo_path'] = $request->file('logo')->store('logos', 'public');
            }
            unset($data['logo']);
            $data['slug'] = Str::slug($data['name']).'-'.$business->id;
        }
        if ($step === 4) {
            $data['instagram_url'] = in_array('instagram', $data['sales_channels'], true) ? $data['instagram_url'] : null;
            $data['marketplace_url'] = in_array('marketplace', $data['sales_channels'], true) ? $data['marketplace_url'] : null;
        }
        if ($step === 5) {
            $data['digital_tools'] = $data['digital_tools'] ?? [];
        }
        if ($step === 6 && in_array('none', $data['telkomsel_products'], true)) {
            $data['telkomsel_products'] = ['none'];
        }

        $business->fill($data);
        $business->onboarding_step = max($business->onboarding_step, $step + 1);
        if ($step === 6) {
            $business->onboarded_at ??= now();
        }
        $business->save();

        return $step < 6
            ? redirect()->route('onboarding.show', $step + 1)
            : redirect()->route('goals.edit');
    }

    public function goals(Request $request)
    {
        $business = $request->user()->business()->first();
        if (! $business?->onboardingComplete()) {
            return redirect()->route('onboarding.show');
        }

        return view('onboarding.goals', [
            'business' => $business,
            'selected' => $business->goals->pluck('goal_key')->all(),
            'revenueGoal' => $business->goals->firstWhere('goal_key', 'increase_revenue'),
            'suggestedRevenue' => config("tumbuh.revenue_ranges.{$business->revenue_range}.mid"),
        ]);
    }

    public function storeGoals(Request $request)
    {
        $business = $request->user()->business()->first();
        abort_unless($business?->onboardingComplete(), 404);

        $data = $request->validate([
            'goals' => ['required', 'array', 'min:1', 'max:3'],
            'goals.*' => [Rule::in(array_keys(config('tumbuh.goals')))],
            'current_revenue' => [Rule::requiredIf(in_array('increase_revenue', (array) $request->input('goals'), true)), 'nullable', 'integer', 'min:0'],
            'target_revenue' => [Rule::requiredIf(in_array('increase_revenue', (array) $request->input('goals'), true)), 'nullable', 'integer', 'gt:current_revenue'],
            'target_period' => [Rule::requiredIf(in_array('increase_revenue', (array) $request->input('goals'), true)), 'nullable', Rule::in([30, 60, 90])],
        ], [
            'goals.max' => 'Pilih maksimal 3 goal agar fokus.',
            'goals.required' => 'Pilih minimal 1 goal.',
            'target_revenue.gt' => 'Target harus lebih besar dari omzet saat ini.',
        ]);

        $business->goals()->whereNotIn('goal_key', $data['goals'])->delete();
        foreach ($data['goals'] as $key) {
            $isRevenue = $key === 'increase_revenue';
            $business->goals()->updateOrCreate(['goal_key' => $key], [
                'metric' => config("tumbuh.goals.{$key}.metric"),
                'baseline_value' => $isRevenue ? $data['current_revenue'] : null,
                'target_value' => $isRevenue ? $data['target_revenue'] : null,
                'target_period_days' => $isRevenue ? (int) $data['target_period'] : null,
                'target_date' => $isRevenue ? now()->addDays((int) $data['target_period']) : null,
            ]);
        }
        $first = ! $business->goals_set_at;
        $business->update(['goals_set_at' => $business->goals_set_at ?? now()]);

        return $first
            ? redirect()->route('diagnostic.start')->with('success', 'Profil & goal tersimpan. Sekarang temukan bottleneck bisnis Anda.')
            : redirect()->route('profile.index')->with('success', 'Goal bisnis diperbarui.');
    }
}
