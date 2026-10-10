<?php

namespace App\Http\Controllers;

use App\Jobs\RunFpGrowthJob;
use App\Models\FpAnalysis;
use App\Models\Menu;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;

class FpGrowthController extends Controller
{
    /** List all analyses + form. */
    public function index(): View
    {
        $analyses = FpAnalysis::with('creator')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('analysis.fp-growth.index', compact('analyses'));
    }

    /** Dispatch a new analysis job. */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'date_from'      => 'required|date',
            'date_to'        => 'required|date|after_or_equal:date_from',
            'min_support'    => 'required|numeric|between:0.01,1',
            'min_confidence' => 'required|numeric|between:0.01,1',
        ]);

        /** @var FpAnalysis $analysis */
        $analysis = FpAnalysis::create([
            ...$validated,
            'status'     => 'pending',
            'created_by' => $request->user()->id,
        ]);

        // Dispatch synchronously so user sees results immediately on this server.
        // Switch to ->dispatch() and run php artisan queue:work for async.
        RunFpGrowthJob::dispatchSync($analysis);

        return redirect()->route('analysis.fp-growth.show', $analysis)
            ->with('success', 'Analisis FP-Growth selesai dijalankan.');
    }

    /** Show result of one analysis. */
    public function show(FpAnalysis $analysis): View
    {
        $itemsets = $analysis->itemsets()
            ->orderByDesc('support')
            ->paginate(20, ['*'], 'iPage');

        $rules = $analysis->rules()
            ->orderByDesc('lift')
            ->paginate(20, ['*'], 'rPage');

        // Resolve menu IDs → names for display
        $menuMap = Menu::withTrashed()
            ->pluck('name', 'id')
            ->toArray();

        $chartRules = $analysis->rules()
            ->orderByDesc('lift')
            ->limit(10)
            ->get()
            ->map(function ($r) use ($menuMap) {
                $ant = collect((array) $r->antecedent)->map(fn($id) => $menuMap[$id] ?? "Menu #$id")->implode(', ');
                $con = collect((array) $r->consequent)->map(fn($id) => $menuMap[$id] ?? "Menu #$id")->implode(', ');
                return [
                    'label'      => $ant . ' → ' . $con,
                    'lift'       => round((float) $r->lift, 3),
                    'confidence' => round((float) $r->confidence * 100, 1),
                ];
            })
            ->values()
            ->all();

        return view('analysis.fp-growth.show', compact('analysis', 'itemsets', 'rules', 'menuMap', 'chartRules'));
    }

    /** Delete an analysis and all its children (cascade). */
    public function destroy(FpAnalysis $analysis): RedirectResponse
    {
        $analysis->delete();

        return redirect()->route('analysis.fp-growth')
            ->with('success', 'Analisis berhasil dihapus.');
    }
}
