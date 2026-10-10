<?php

namespace App\Jobs;

use App\Models\FpAnalysis;
use App\Services\FpGrowthService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;
use Throwable;

class RunFpGrowthJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 300; // 5 minutes

    public function __construct(public readonly FpAnalysis $analysis) {}

    /**
     * Execute the job.
     */
    public function handle(FpGrowthService $service): void
    {
        $this->analysis->update(['status' => 'running', 'started_at' => now()]);

        try {
            $service
                ->setDateRange(
                    $this->analysis->date_from ? \Carbon\Carbon::parse($this->analysis->date_from) : null,
                    $this->analysis->date_to   ? \Carbon\Carbon::parse($this->analysis->date_to)   : null
                )
                ->run($this->analysis);

            $this->analysis->update(['status' => 'done', 'finished_at' => now()]);
        } catch (Throwable $e) {
            Log::error('FP-Growth job failed', [
                'analysis_id' => $this->analysis->id,
                'message'     => $e->getMessage(),
            ]);
            $this->analysis->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at'   => now(),
            ]);
            throw $e;
        }
    }
}
