<?php

namespace App\Http\Controllers;

use App\Services\Telkomsel\KuncieAdapter;
use App\Services\KpiService;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** AI Growth Assistant — deterministic insight engine with AI-style UX. */
class AssistantController extends Controller
{
    public const INTENTS = [
        'sales_drop' => 'Penjualan saya turun. Apa yang harus saya lakukan?',
        'focus' => 'Apa yang harus saya fokuskan bulan ini?',
        'profit' => 'Apakah bisnis saya benar-benar untung?',
        'stock' => 'Produk mana yang perlu saya restock?',
    ];

    public function show(Request $request, RecommendationService $recommendations, KpiService $kpi, KuncieAdapter $kuncie)
    {
        $business = $this->business();
        $rec = $business->currentRecommendation;
        $intent = session('intent');

        return view('growth.assistant', [
            'business' => $business,
            'recommendation' => $rec,
            'score' => $business->latestScore,
            'answer' => $intent ? $recommendations->answer($business, $intent) : null,
            'intents' => self::INTENTS,
            'learning' => $rec ? $kuncie->learningPlan($rec->bottleneck) : null,
            'alreadyUses' => fn ($key) => $recommendations->alreadyUses($business, $key),
            'native' => RecommendationService::NATIVE,
        ]);
    }

    public function ask(Request $request)
    {
        $request->validate(['intent' => ['required', Rule::in(array_keys(self::INTENTS))]]);

        return redirect()->route('assistant')->with('intent', $request->input('intent'))->withFragment('ask');
    }
}
