<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\InventoryAnalyticsService;
use App\Services\InventoryService;
use App\Services\TierService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Growth Inventory (TUMBUH-native capability). */
class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventory, private InventoryAnalyticsService $analytics) {}

    public function index(TierService $tiers)
    {
        $business = $this->business();

        return view('inventory.index', [
            'business' => $business,
            'stats' => $this->analytics->stats($business),
            'rows' => $this->analytics->products($business),
            'top' => $this->analytics->topSelling($business),
            'insights' => $this->analytics->insights($business),
            'canValue' => $tiers->allows($business, 'inventory.value'),
            'predictive' => $tiers->allows($business, 'inventory.predictive'),
        ]);
    }

    public function create()
    {
        return view('inventory.create', ['business' => $this->business()]);
    }

    public function store(Request $request)
    {
        $business = $this->business();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'sku' => ['required', 'string', 'max:40', Rule::unique('products')->where('business_id', $business->id)],
            'category' => ['nullable', 'string', 'max:80'],
            'purchase_price' => ['required', 'integer', 'min:0'],
            'selling_price' => ['required', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'min_stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'supplier' => ['nullable', 'string', 'max:120'],
            'unit' => ['required', 'string', 'max:20'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image']);

        $product = $this->inventory->createProduct($business, $data);

        return redirect()->route('inventory.show', $product)->with('success', 'Produk ditambahkan dengan stok awal '.$product->stock.' '.$product->unit.'.');
    }

    public function show(Product $product, TierService $tiers)
    {
        $this->authorizeOwn($product);
        $row = $this->analytics->products($this->business())->first(fn ($r) => $r['product']->id === $product->id);

        return view('inventory.show', [
            'business' => $this->business(),
            'product' => $product,
            'row' => $row,
            'movements' => $product->movements()->with('financialEntry')->limit(20)->get(),
            'predictive' => $tiers->allows($this->business(), 'inventory.predictive'),
        ]);
    }

    public function stockIn(Request $request, Product $product)
    {
        $this->authorizeOwn($product);
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'unit_cost' => ['nullable', 'integer', 'min:0'],
            'record_expense' => ['nullable', 'boolean'],
        ]);
        $this->inventory->restock($product, $data['quantity'], $data['unit_cost'] ?? null, null, 'manual', $request->boolean('record_expense', true));

        return back()->with('success', 'Stok masuk +'.$data['quantity'].' '.$product->unit.($request->boolean('record_expense', true) ? ' — pengeluaran tercatat di Growth Finance.' : '.'));
    }

    public function sale(Request $request, Product $product)
    {
        $this->authorizeOwn($product);
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'payment_method' => ['required', Rule::in(array_keys(config('tumbuh.payment_methods')))],
            'customer' => ['nullable', 'string', 'max:120'],
        ]);
        $this->inventory->recordSale($product, $data['quantity'], null, $data['payment_method'], 'manual', $data['customer'] ?? null);

        return back()->with('success', 'Penjualan tercatat: stok −'.$data['quantity'].', omzet +'.rupiah($data['quantity'] * $product->selling_price).', COGS +'.rupiah($data['quantity'] * $product->purchase_price).'.');
    }

    public function adjust(Request $request, Product $product)
    {
        $this->authorizeOwn($product);
        $data = $request->validate(['stock' => ['required', 'integer', 'min:0'], 'note' => ['nullable', 'string', 'max:120']]);
        $this->inventory->adjust($product, $data['stock'], $data['note'] ?? 'Stock opname');

        return back()->with('success', 'Stok disesuaikan menjadi '.$data['stock'].' '.$product->unit.'.');
    }
}
