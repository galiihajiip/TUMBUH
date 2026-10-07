<?php

namespace App\Http\Controllers;

use App\Models\ExpenseReceipt;
use App\Services\FinanceService;
use App\Services\FinancialAnalyticsService;
use App\Services\TierService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/** Growth Finance (TUMBUH-native capability, not a Telkomsel product). */
class FinanceController extends Controller
{
    public function __construct(private FinancialAnalyticsService $analytics, private FinanceService $finance) {}

    public function index(TierService $tiers)
    {
        $business = $this->business();

        return view('finance.index', [
            'business' => $business,
            'summary' => $this->analytics->summary($business),
            'mtd' => $this->analytics->monthToDate($business),
            'series' => $this->analytics->series($business),
            'entries' => $business->financialEntries()->with('product')->latest('entry_date')->latest('id')->limit(12)->get(),
            'insight' => $this->analytics->insight($business),
            'canReport' => $tiers->allows($business, 'finance.pnl'),
            'canScan' => $tiers->allows($business, 'finance.ocr'),
        ]);
    }

    public function createIncome()
    {
        return view('finance.income', ['business' => $this->business(), 'products' => $this->business()->products()->orderBy('name')->get()]);
    }

    public function storeIncome(Request $request)
    {
        $business = $this->business();
        $data = $request->validate([
            'entry_date' => ['required', 'date', 'before_or_equal:today'],
            'category' => ['required', Rule::in(array_keys(config('tumbuh.income_categories')))],
            'amount' => ['required', 'integer', 'min:1', 'max:10000000000'],
            'customer' => ['nullable', 'string', 'max:120'],
            'product_id' => ['nullable', Rule::exists('products', 'id')->where('business_id', $business->id)],
            'description' => ['nullable', 'string', 'max:160'],
            'payment_method' => ['required', Rule::in(array_keys(config('tumbuh.payment_methods')))],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
        $this->finance->record($business, $data + ['type' => 'income']);

        return redirect()->route('finance.index')->with('success', 'Pemasukan '.rupiah($data['amount']).' tercatat.');
    }

    public function createExpense()
    {
        return view('finance.expense', ['business' => $this->business(), 'canScan' => app(TierService::class)->allows($this->business(), 'finance.ocr')]);
    }

    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'entry_date' => ['required', 'date', 'before_or_equal:today'],
            'category' => ['required', Rule::in(array_keys(config('tumbuh.expense_categories')))],
            'amount' => ['required', 'integer', 'min:1', 'max:10000000000'],
            'description' => ['required', 'string', 'max:160'],
            'payment_method' => ['required', Rule::in(array_keys(config('tumbuh.payment_methods')))],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
        $this->finance->record($this->business(), $data + ['type' => 'expense']);

        return redirect()->route('finance.index')->with('success', 'Pengeluaran '.rupiah($data['amount']).' tercatat.');
    }

    public function scan()
    {
        return view('finance.scan', ['business' => $this->business()]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'receipt' => ['required_without:sample', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'sample' => ['nullable', 'integer', 'min:0', 'max:3'],
        ], ['receipt.required_without' => 'Unggah foto struk atau pilih contoh struk demo.']);

        $receipt = $this->finance->scanReceipt($this->business(), $request->file('receipt'), $request->filled('sample') ? (int) $request->input('sample') : null);

        return redirect()->route('finance.scan.review', $receipt);
    }

    public function reviewReceipt(ExpenseReceipt $receipt)
    {
        $this->authorizeOwn($receipt);

        return view('finance.scan-review', ['business' => $this->business(), 'receipt' => $receipt]);
    }

    public function confirmReceipt(Request $request, ExpenseReceipt $receipt)
    {
        $this->authorizeOwn($receipt);
        abort_if($receipt->status === 'confirmed', 409);

        $data = $request->validate([
            'merchant' => ['required', 'string', 'max:160'],
            'entry_date' => ['required', 'date', 'before_or_equal:today'],
            'category' => ['required', Rule::in(array_keys(config('tumbuh.expense_categories')))],
            'amount' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', Rule::in(array_keys(config('tumbuh.payment_methods')))],
        ]);
        $this->finance->confirmReceipt($receipt, $data);

        return redirect()->route('finance.index')->with('success', 'Struk dikonfirmasi & dikategorikan sebagai '.config('tumbuh.expense_categories.'.$data['category']).'.');
    }

    public function report(Request $request, TierService $tiers)
    {
        $business = $this->business();
        $month = $request->filled('month') ? Carbon::createFromFormat('Y-m', $request->query('month'))->startOfMonth() : $this->analytics->latestMonth($business);
        $cur = $this->analytics->month($business, $month);
        $prev = $this->analytics->month($business, $month->copy()->subMonthNoOverflow());
        $months = $business->financialEntries()->selectRaw("strftime('%Y-%m', entry_date) as ym")->distinct()->orderByDesc('ym')->pluck('ym');

        return view('finance.report', [
            'business' => $business,
            'cur' => $cur,
            'prev' => $prev,
            'months' => $months,
            'series' => $this->analytics->series($business),
            'drivers' => $this->analytics->costDrivers($business),
            'insight' => $this->analytics->insight($business),
            'health' => $tiers->allows($business, 'finance.advanced') ? $this->analytics->healthScore($business) : null,
            'advanced' => $tiers->allows($business, 'finance.advanced'),
            'analytics' => $this->analytics,
        ]);
    }
}
