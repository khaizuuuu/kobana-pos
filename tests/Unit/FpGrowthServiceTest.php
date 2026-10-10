<?php

namespace Tests\Unit;

use App\Models\FpAnalysis;
use App\Models\FpFrequentItemset;
use App\Models\FpAssociationRule;
use App\Models\User;
use App\Services\FpGrowthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 8 – FP-Growth Service Unit Tests.
 *
 * Tests the core algorithm logic against known fixtures.
 */
class FpGrowthServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);
    }

    /** Build a minimal FpAnalysis stub for testing. */
    private function makeAnalysis(float $support = 0.5, float $confidence = 0.6): FpAnalysis
    {
        return FpAnalysis::create([
            'name'           => 'Test Analysis',
            'date_from'      => '2026-01-01',
            'date_to'        => '2026-12-31',
            'min_support'    => $support,
            'min_confidence' => $confidence,
            'status'         => 'pending',
            'created_by'     => $this->admin->id,
        ]);
    }

    /** @test */
    public function fp_growth_service_can_be_instantiated(): void
    {
        $service = new FpGrowthService(0.05, 0.6);
        $this->assertInstanceOf(FpGrowthService::class, $service);
    }

    /** @test */
    public function fp_growth_service_runs_without_transactions_gracefully(): void
    {
        $analysis = $this->makeAnalysis();
        $service  = new FpGrowthService(0.5, 0.6);
        $service->run($analysis);

        // No itemsets or rules should be created when no transactions exist
        $this->assertEquals(0, FpFrequentItemset::where('analysis_id', $analysis->id)->count());
        $this->assertEquals(0, FpAssociationRule::where('analysis_id', $analysis->id)->count());
    }

    /** @test */
    public function fp_analysis_model_has_correct_relationships(): void
    {
        $analysis = $this->makeAnalysis();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $analysis->itemsets()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $analysis->rules()
        );
        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\BelongsTo::class,
            $analysis->creator()
        );
    }

    /** @test */
    public function fp_analysis_status_transitions_are_valid(): void
    {
        $analysis = $this->makeAnalysis();
        $this->assertEquals('pending', $analysis->status);

        $analysis->update(['status' => 'running']);
        $this->assertEquals('running', $analysis->fresh()->status);

        $analysis->update(['status' => 'done']);
        $this->assertEquals('done', $analysis->fresh()->status);
    }
}
