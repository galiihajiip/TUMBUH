<?php

namespace App\Services;

use App\Models\Business;
use App\Models\ExpenseReceipt;
use App\Models\FinancialEntry;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Growth Finance writes: manual income/expense and receipt scanning.
 * OCR is a local, deterministic simulation — no external AI service.
 */
class FinanceService
{
    /** Keyword → expense category (auto-categorisation). */
    private const KEYWORDS = [
        'raw_material' => ['tepung', 'gula', 'susu', 'kopi', 'mentega', 'telur', 'coklat', 'keju', 'beans', 'krim', 'matcha', 'kain', 'benang'],
        'marketing' => ['iklan', 'ads', 'promo', 'brosur', 'banner', 'cetak'],
        'transportation' => ['bensin', 'pertalite', 'ojek', 'kurir', 'ongkir', 'parkir'],
        'utilities' => ['listrik', 'pln', 'air', 'pdam', 'internet', 'pulsa', 'gas', 'elpiji'],
        'equipment' => ['mixer', 'oven', 'blender', 'grinder', 'rak', 'etalase'],
        'inventory' => ['kemasan', 'box', 'cup', 'packaging', 'stok'],
    ];

    /** Sample receipts used by the OCR simulation. */
    private const SAMPLES = [
        ['merchant' => 'Toko Bahan Kue Sentosa', 'items' => [['name' => 'Tepung Terigu Protein Sedang 25kg', 'qty' => 1, 'price' => 245000], ['name' => 'Gula Pasir 10kg', 'qty' => 2, 'price' => 162000], ['name' => 'Mentega Tawar 2kg', 'qty' => 1, 'price' => 138000]], 'tax' => 0],
        ['merchant' => 'Sumber Kopi Nusantara', 'items' => [['name' => 'Kopi Arabika Beans 1kg', 'qty' => 3, 'price' => 185000], ['name' => 'Susu UHT 1L (karton)', 'qty' => 2, 'price' => 215000]], 'tax' => 0],
        ['merchant' => 'PLN Token Listrik', 'items' => [['name' => 'Token listrik 1.300VA', 'qty' => 1, 'price' => 500000]], 'tax' => 0],
        ['merchant' => 'Grosir Kemasan Jaya', 'items' => [['name' => 'Cup plastik 16oz (isi 50)', 'qty' => 10, 'price' => 32000], ['name' => 'Paper box kue 20x20', 'qty' => 5, 'price' => 45000]], 'tax' => 0],
    ];

    public function record(Business $business, array $data): FinancialEntry
    {
        return $business->financialEntries()->create([
            'type' => $data['type'],
            'entry_date' => $data['entry_date'],
            'category' => $data['category'],
            'amount' => (int) $data['amount'],
            'customer' => $data['customer'] ?? null,
            'product_id' => $data['product_id'] ?? null,
            'description' => $data['description'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'notes' => $data['notes'] ?? null,
            'source' => $data['source'] ?? 'manual',
        ]);
    }

    /**
     * Store the uploaded receipt and produce a simulated OCR extraction.
     * The sample is picked from the file hash, so the same file always
     * yields the same result (reproducible demo).
     */
    public function scanReceipt(Business $business, ?UploadedFile $file, ?int $sampleIndex = null): ExpenseReceipt
    {
        $path = $file?->store('receipts/'.$business->id, 'local');
        $index = $sampleIndex ?? ($file ? hexdec(substr(md5_file($file->getRealPath()), 0, 6)) % count(self::SAMPLES) : 0);
        $sample = self::SAMPLES[$index % count(self::SAMPLES)];

        $subtotal = collect($sample['items'])->sum(fn ($i) => $i['qty'] * $i['price']);
        $text = strtolower($sample['merchant'].' '.collect($sample['items'])->pluck('name')->implode(' '));

        return $business->receipts()->create([
            'file_path' => $path,
            'status' => 'pending',
            'parsed' => [
                'merchant' => $sample['merchant'],
                'date' => now()->toDateString(),
                'items' => $sample['items'],
                'subtotal' => $subtotal,
                'tax' => $sample['tax'],
                'total' => $subtotal + $sample['tax'],
                'suggested_category' => $this->categorize($text),
                'confidence' => 0.92,
                'engine' => 'Simulated OCR (local demo)',
            ],
        ]);
    }

    public function categorize(string $text): string
    {
        foreach (self::KEYWORDS as $category => $words) {
            foreach ($words as $w) {
                if (str_contains($text, $w)) {
                    return $category;
                }
            }
        }

        return 'other';
    }

    public function confirmReceipt(ExpenseReceipt $receipt, array $data): FinancialEntry
    {
        return DB::transaction(function () use ($receipt, $data) {
            $entry = $this->record($receipt->business, [
                'type' => 'expense',
                'entry_date' => $data['entry_date'],
                'category' => $data['category'],
                'amount' => $data['amount'],
                'description' => $data['merchant'],
                'notes' => 'Dari scan struk: '.collect($receipt->parsed['items'])->pluck('name')->implode(', '),
                'payment_method' => $data['payment_method'] ?? 'cash',
                'source' => 'receipt_ocr',
            ]);
            $receipt->update(['status' => 'confirmed', 'financial_entry_id' => $entry->id]);

            return $entry;
        });
    }
}
