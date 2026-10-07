<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Diagnostic;
use App\Models\DiagnosticQuestion;
use App\Models\DiagnosticResponse;
use App\Models\KpiSnapshot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Guided Growth Diagnostic: question flow, autosave, normalisation of
 * raw answers into 0–100 scores, and baseline KPI confirmation.
 */
class DiagnosticService
{
    public function __construct(
        private GrowthScoreService $scores,
        private RecommendationService $recommendations,
        private ActionPlanService $plans,
        private NotificationService $notifications,
    ) {}

    public function questions(): Collection
    {
        return DiagnosticQuestion::active()->get();
    }

    /** Resume the open diagnostic or start a new cycle. */
    public function current(Business $business): Diagnostic
    {
        $open = $business->diagnostics()->whereIn('status', ['in_progress', 'answered'])->latest('id')->first();

        return $open ?? $business->diagnostics()->create([
            'cycle' => $business->growth_cycle,
            'status' => 'in_progress',
        ]);
    }

    public function answers(Diagnostic $diagnostic): Collection
    {
        return $diagnostic->responses()->get()->keyBy('diagnostic_question_id');
    }

    /** Validate and persist one answer (used by both autosave and Next). */
    public function saveAnswer(Diagnostic $diagnostic, DiagnosticQuestion $question, mixed $value): DiagnosticResponse
    {
        $value = $this->validateValue($question, $value);

        return DiagnosticResponse::updateOrCreate(
            ['diagnostic_id' => $diagnostic->id, 'diagnostic_question_id' => $question->id],
            ['value' => $value, 'normalized_score' => $this->normalize($question, $value)],
        );
    }

    public function validateValue(DiagnosticQuestion $question, mixed $value): mixed
    {
        $fail = fn (string $msg) => throw ValidationException::withMessages(['answer' => $msg]);

        switch ($question->type) {
            case 'single':
                $allowed = collect($question->options)->pluck('value')->all();
                if (! is_string($value) || ! in_array($value, $allowed, true)) {
                    $fail('Pilih salah satu jawaban untuk melanjutkan.');
                }

                return $value;
            case 'multi':
                $allowed = collect($question->options)->pluck('value')->all();
                $value = array_values(array_unique(array_filter((array) $value, 'is_string')));
                if ($value === [] || array_diff($value, $allowed) !== []) {
                    $fail('Pilih minimal satu jawaban.');
                }

                return $value;
            default: // number | percent | currency
                if (! is_numeric($value) || $value < 0) {
                    $fail('Masukkan angka yang valid (0 atau lebih).');
                }
                if ($question->type === 'percent' && $value > 100) {
                    $fail('Persentase maksimal 100.');
                }

                return (float) $value;
        }
    }

    /** Deterministic 0–100 normalisation of a raw answer. */
    public function normalize(DiagnosticQuestion $question, mixed $value): float
    {
        $options = collect($question->options ?? [])->keyBy('value');

        return match ($question->type) {
            'single' => (float) ($options[$value]['score'] ?? 0),
            'multi' => (float) min(100, collect((array) $value)->sum(fn ($v) => $options[$v]['score'] ?? 0)),
            default => (float) max(5, min(100, ((float) $value / max(1, (float) ($question->scoring['good'] ?? 100))) * 100)),
        };
    }

    public function progress(Diagnostic $diagnostic): array
    {
        $total = $this->questions()->count();
        $answered = $diagnostic->responses()->count();

        return ['answered' => $answered, 'total' => $total, 'percent' => $total ? (int) round($answered / $total * 100) : 0];
    }

    public function markAnswered(Diagnostic $diagnostic): void
    {
        $missing = $this->questions()->pluck('id')->diff($diagnostic->responses()->pluck('diagnostic_question_id'));
        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages(['answer' => 'Masih ada '.$missing->count().' pertanyaan yang belum dijawab.']);
        }

        $diagnostic->update(['status' => 'answered', 'answered_at' => now()]);
    }

    /** Pre-fill values for the baseline confirmation screen. */
    public function suggestedBaseline(Business $business, Diagnostic $diagnostic): array
    {
        $latest = $business->kpiSnapshots()->reorder()->orderByDesc('period')->first();
        if ($latest) {
            return $latest->only(['revenue', 'new_customers', 'total_customers', 'conversion_rate', 'repeat_rate', 'marketing_spend']) + ['source' => $latest->source];
        }

        $answers = $diagnostic->responses()->with('question')->get()->keyBy(fn ($r) => $r->question->key);
        $repeatMap = ['lt10' => 8, '10_20' => 15, '20_30' => 22, '30_45' => 35, 'gt45' => 50];
        $convMap = ['le1' => 1.0, '2_3' => 2.5, '4_5' => 4.5, 'gt5' => 6.0];
        $newMap = ['lt20' => 15, '20_50' => 35, '50_100' => 75, '100_200' => 140, 'gt200' => 250];

        return [
            'revenue' => (int) config("tumbuh.revenue_ranges.{$business->revenue_range}.mid", 10_000_000),
            'new_customers' => $newMap[$answers['acq_new_customers']->value ?? ''] ?? 50,
            'total_customers' => (int) ($business->monthly_customers ?? 200),
            'conversion_rate' => $convMap[$answers['conv_rate']->value ?? ''] ?? 2.5,
            'repeat_rate' => $repeatMap[$answers['ret_repeat']->value ?? ''] ?? 20,
            'marketing_spend' => 0,
            'source' => 'estimated',
        ];
    }

    /**
     * Confirm baseline KPIs, then compute Growth Score, bottleneck,
     * AI recommendation and a 30-day action plan in one transaction.
     */
    public function confirmBaseline(Business $business, Diagnostic $diagnostic, array $kpi): Diagnostic
    {
        return DB::transaction(function () use ($business, $diagnostic, $kpi) {
            $period = now()->startOfMonth()->subMonth()->toDateString();
            $isFirst = ! $business->kpiSnapshots()->exists();

            $existing = $business->kpiSnapshots()->whereDate('period', $period)->first();
            $snapshot = $existing ?? new KpiSnapshot(['business_id' => $business->id, 'period' => $period]);
            $snapshot->fill([
                'revenue' => (int) $kpi['revenue'],
                'new_customers' => (int) $kpi['new_customers'],
                'total_customers' => (int) $kpi['total_customers'],
                'conversion_rate' => (float) $kpi['conversion_rate'],
                'repeat_rate' => (float) $kpi['repeat_rate'],
                'marketing_spend' => (int) $kpi['marketing_spend'],
                'source' => 'self_reported',
                'note' => 'Dikonfirmasi saat Growth Diagnostic siklus '.$diagnostic->cycle,
            ]);
            if ($isFirst) {
                $snapshot->is_baseline = true;
            }
            $snapshot->save();

            $diagnostic->update([
                'status' => 'completed',
                'baseline_confirmed_at' => now(),
                'kpi_snapshot_id' => $snapshot->id,
            ]);

            $score = $this->scores->fromDiagnostic($business, $diagnostic->fresh());
            $snapshot->update(['growth_score' => $score->overall]);

            $recommendation = $this->recommendations->generate($business->fresh(), $score);
            $this->plans->generate($business->fresh(), $recommendation);

            $this->notifications->push($business, 'ai', 'Kami menemukan bottleneck pertumbuhan Anda',
                'Bottleneck utama: '.config("tumbuh.dimensions.{$score->bottleneck}.label").'. Lihat rekomendasi AI dan rencana 30 hari Anda.',
                'Lihat rekomendasi', route('assistant'));

            return $diagnostic->fresh();
        });
    }
}
