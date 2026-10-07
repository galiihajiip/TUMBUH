<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Demo\DemoScenarioBuilder;
use App\Services\DemoSimulationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Presentation helper. Only demo accounts can be entered here; actions
 * run the same services as the product, so cause → effect is real.
 */
class DemoController extends Controller
{
    public const ACTIONS = [
        'complete_mission' => ['label' => 'Complete Mission', 'icon' => 'task_alt', 'help' => 'Check-in misi aktif → plan maju, achievement, reward.'],
        'launch_campaign' => ['label' => 'Launch Campaign', 'icon' => 'campaign', 'help' => 'Kampanye MyAds tersimulasi: reach, klik, leads, konversi, ROAS.'],
        'record_sale' => ['label' => 'Record Sale', 'icon' => 'point_of_sale', 'help' => 'Penjualan 3 unit → stok, omzet & COGS.'],
        'restock' => ['label' => 'Restock', 'icon' => 'inventory', 'help' => 'Restock produk berisiko → stok & cash-out.'],
        'run_review' => ['label' => 'Run AI Review', 'icon' => 'auto_awesome', 'help' => 'Analisis KPI → insight & next action.'],
        'recalculate' => ['label' => 'Recalculate Growth Score', 'icon' => 'calculate', 'help' => 'Snapshot skor + bottleneck baru.'],
        'close_cycle' => ['label' => 'Close 30-Day Cycle', 'icon' => 'event_repeat', 'help' => 'Aktivitas → KPI bulan berikutnya → review → skor → rencana baru.'],
    ];

    public function __construct()
    {
        abort_unless(config('tumbuh.demo.enabled'), 404);
    }

    public function index()
    {
        $business = auth()->user()?->business;

        return view('demo.index', [
            'accounts' => DemoScenarioBuilder::ACCOUNTS,
            'actions' => self::ACTIONS,
            'business' => $business?->onboardingComplete() ? $business : null,
        ]);
    }

    public function login(Request $request, string $key)
    {
        abort_unless(isset(DemoScenarioBuilder::ACCOUNTS[$key]), 404);
        $user = User::where('email', DemoScenarioBuilder::ACCOUNTS[$key]['email'])->where('is_demo', true)->firstOrFail();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('overview')->with('success', 'Masuk sebagai '.$user->name.' (Demo Environment).');
    }

    public function reset(Request $request, string $key, DemoScenarioBuilder $builder)
    {
        abort_unless(isset(DemoScenarioBuilder::ACCOUNTS[$key]), 404);
        $wasCurrent = auth()->user()?->email === DemoScenarioBuilder::ACCOUNTS[$key]['email'];
        $user = $builder->build($key);

        if ($wasCurrent) {
            Auth::login($user);
            $request->session()->regenerate();
        }

        return back()->with('success', 'Skenario '.DemoScenarioBuilder::ACCOUNTS[$key]['business'].' direset ke kondisi awal.');
    }

    public function action(string $action, DemoSimulationService $demo)
    {
        abort_unless(isset(self::ACTIONS[$action]), 404);
        $business = $this->business();

        $message = match ($action) {
            'complete_mission' => $demo->completeMission($business) ?? 'Tidak ada misi aktif.',
            'launch_campaign' => $demo->launchCampaign($business),
            'record_sale' => $demo->recordSale($business) ?? 'Tidak ada produk dengan stok cukup.',
            'restock' => $demo->restock($business) ?? 'Tidak ada produk yang perlu direstock.',
            'run_review' => $demo->runReview($business) ?? 'Belum ada data KPI untuk direview.',
            'recalculate' => $demo->recalculate($business) ?? 'Selesaikan Growth Diagnostic terlebih dahulu.',
            'close_cycle' => $demo->closeCycle($business) ?? 'Belum ada baseline KPI.',
        };

        return back()->with('success', $message);
    }

    /** Controlled tier preview for the presentation (labelled, reversible). */
    public function tier(Request $request)
    {
        $data = $request->validate(['tier' => ['required', Rule::in(array_keys(config('tumbuh.tiers')))]]);
        $business = $this->business();
        abort_unless($business->user?->is_demo, 403, 'Tier preview hanya untuk akun demo.');
        $business->update(['tier' => $data['tier'], 'tier_note' => 'Demo Mode tier preview', 'tier_expires_at' => null]);

        return back()->with('success', 'Tier diubah ke '.ucfirst($data['tier']).' (Demo Mode). Aturan akses tier tetap berlaku.');
    }
}
