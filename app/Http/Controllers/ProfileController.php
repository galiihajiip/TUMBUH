<?php

namespace App\Http\Controllers;

use App\Services\SolutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/** My Business Data: personal & business data, connected services, consent, security. */
class ProfileController extends Controller
{
    public function index(Request $request, SolutionService $solutions)
    {
        $business = $this->business();
        $user = $request->user();

        return view('profile.index', [
            'user' => $user,
            'business' => $business->load('goals'),
            'consents' => $user->consents->keyBy('consent_type'),
            'activations' => $solutions->activationsFor($business),
            'sessions' => DB::table('sessions')->where('user_id', $user->id)->orderByDesc('last_activity')->get(),
            'currentSession' => $request->session()->getId(),
            'counts' => [
                'kpi' => $business->kpiSnapshots()->count(),
                'finance' => $business->financialEntries()->count(),
                'products' => $business->products()->count(),
                'responses' => $business->diagnostics()->withCount('responses')->get()->sum('responses_count'),
                'missions' => $business->missions()->has('submissions')->count(),
                'campaigns' => $business->campaigns()->count(),
            ],
        ]);
    }

    public function updatePersonal(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:/^(\+62|62|0)8[0-9]{7,12}$/'],
            'role' => ['required', Rule::in(array_keys(config('tumbuh.roles')))],
        ]);
        $user->update($data);

        return back()->with('success', 'Data pribadi diperbarui.');
    }

    public function updateBusiness(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', Rule::in(array_keys(config('tumbuh.categories')))],
            'city' => ['required', 'string', 'max:80'],
            'district' => ['nullable', 'string', 'max:80'],
            'employees' => ['required', 'integer', 'min:0'],
            'revenue_range' => ['required', Rule::in(array_keys(config('tumbuh.revenue_ranges')))],
        ]);
        $this->business()->update($data);

        return back()->with('success', 'Data bisnis diperbarui.');
    }

    public function updateConsents(Request $request)
    {
        $user = $request->user();
        foreach (array_keys(config('tumbuh.consents')) as $type) {
            $granted = $request->boolean("consents.{$type}");
            $consent = $user->consents()->firstOrNew(['consent_type' => $type]);
            if (! $consent->exists || $consent->granted !== $granted) {
                $consent->fill([
                    'granted' => $granted,
                    'granted_at' => $granted ? now() : $consent->granted_at,
                    'revoked_at' => $granted ? null : now(),
                ])->save();
            }
        }

        return back()->with('success', 'Preferensi persetujuan data disimpan.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);
        if (! Hash::check($data['current_password'], $request->user()->password)) {
            throw ValidationException::withMessages(['current_password' => 'Password saat ini salah.']);
        }
        $request->user()->update(['password' => $data['password']]);

        return back()->with('success', 'Password diperbarui.');
    }

    public function logoutOthers(Request $request)
    {
        DB::table('sessions')->where('user_id', $request->user()->id)->where('id', '!=', $request->session()->getId())->delete();

        return back()->with('success', 'Sesi di perangkat lain telah diakhiri.');
    }
}
