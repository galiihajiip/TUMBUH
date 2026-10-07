<?php

namespace App\Http\Controllers;

use App\Services\DiagnosticService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Growth Diagnostic: one question at a time, autosave, back/next,
 * review before submit, then baseline KPI confirmation → score.
 */
class DiagnosticController extends Controller
{
    public function __construct(private DiagnosticService $diagnostics) {}

    public function start()
    {
        $business = $this->business();
        $diagnostic = $this->diagnostics->current($business);
        if ($diagnostic->status === 'answered') {
            return redirect()->route('diagnostic.baseline');
        }

        $answered = $diagnostic->responses()->pluck('diagnostic_question_id');
        $index = $this->diagnostics->questions()->search(fn ($q) => ! $answered->contains($q->id));

        return $index === false
            ? redirect()->route('diagnostic.review')
            : redirect()->route('diagnostic.question', $index + 1);
    }

    /** Growth Loop: start a new diagnostic cycle. */
    public function newCycle()
    {
        $business = $this->business();
        $business->diagnostics()->whereIn('status', ['in_progress', 'answered'])->delete();
        $business->diagnostics()->create(['cycle' => $business->growth_cycle, 'status' => 'in_progress']);

        return redirect()->route('diagnostic.question', 1)->with('success', 'Diagnostic siklus baru dimulai. Jawaban sebelumnya tidak berubah.');
    }

    public function question(int $index)
    {
        $business = $this->business();
        $diagnostic = $this->diagnostics->current($business);
        $questions = $this->diagnostics->questions();
        abort_if($index < 1 || $index > $questions->count(), 404);

        $question = $questions[$index - 1];
        $answer = $this->diagnostics->answers($diagnostic)->get($question->id)?->value;

        return view('diagnostic.question', [
            'question' => $question,
            'index' => $index,
            'total' => $questions->count(),
            'answer' => $answer,
            'progress' => $this->diagnostics->progress($diagnostic),
            'sections' => $questions->pluck('section')->unique()->values(),
        ]);
    }

    public function answer(Request $request, int $index)
    {
        $business = $this->business();
        $diagnostic = $this->diagnostics->current($business);
        $questions = $this->diagnostics->questions();
        abort_if($index < 1 || $index > $questions->count(), 404);

        if ($request->input('nav') === 'back') {
            if ($request->filled('answer')) {
                try {
                    $this->diagnostics->saveAnswer($diagnostic, $questions[$index - 1], $request->input('answer'));
                } catch (ValidationException) {
                    // Going back never blocks on validation.
                }
            }

            return redirect()->route('diagnostic.question', max(1, $index - 1));
        }

        $this->diagnostics->saveAnswer($diagnostic, $questions[$index - 1], $request->input('answer'));

        return $index < $questions->count()
            ? redirect()->route('diagnostic.question', $index + 1)
            : redirect()->route('diagnostic.review');
    }

    /** JSON autosave used by Alpine on every selection change. */
    public function autosave(Request $request)
    {
        $request->validate(['question_id' => ['required', 'integer']]);
        $diagnostic = $this->diagnostics->current($this->business());
        $question = $this->diagnostics->questions()->firstWhere('id', (int) $request->input('question_id'));
        abort_unless($question, 404);

        $response = $this->diagnostics->saveAnswer($diagnostic, $question, $request->input('answer'));

        return response()->json(['saved' => true, 'progress' => $this->diagnostics->progress($diagnostic), 'at' => $response->updated_at->format('H:i:s')]);
    }

    public function review()
    {
        $diagnostic = $this->diagnostics->current($this->business());
        $answers = $this->diagnostics->answers($diagnostic);

        return view('diagnostic.review', [
            'questions' => $this->diagnostics->questions(),
            'answers' => $answers,
            'progress' => $this->diagnostics->progress($diagnostic),
        ]);
    }

    public function submit()
    {
        $diagnostic = $this->diagnostics->current($this->business());
        $this->diagnostics->markAnswered($diagnostic);

        return redirect()->route('diagnostic.baseline');
    }

    public function baseline()
    {
        $business = $this->business();
        $diagnostic = $this->diagnostics->current($business);
        if ($diagnostic->status !== 'answered') {
            return redirect()->route('diagnostic.start');
        }

        return view('diagnostic.baseline', [
            'kpi' => $this->diagnostics->suggestedBaseline($business, $diagnostic),
            'period' => now()->startOfMonth()->subMonth(),
        ]);
    }

    public function confirmBaseline(Request $request)
    {
        $business = $this->business();
        $diagnostic = $this->diagnostics->current($business);
        abort_unless($diagnostic->status === 'answered', 409);

        $kpi = $request->validate([
            'revenue' => ['required', 'integer', 'min:0', 'max:100000000000'],
            'new_customers' => ['required', 'integer', 'min:0', 'max:1000000'],
            'total_customers' => ['required', 'integer', 'min:0', 'max:10000000'],
            'conversion_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'repeat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'marketing_spend' => ['required', 'integer', 'min:0', 'max:10000000000'],
        ]);

        $this->diagnostics->confirmBaseline($business, $diagnostic, $kpi);

        return redirect()->route('growth.score')->with('success', 'Diagnostic selesai. Inilah Growth Score Anda.');
    }
}
