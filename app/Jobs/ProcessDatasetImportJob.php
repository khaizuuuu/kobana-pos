<?php

namespace App\Jobs;

use App\Services\DatasetImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDatasetImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes timeout for large datasets

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $filePath,
        public ?int $userId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(DatasetImportService $importService): void
    {
        Log::info("Memulai ProcessDatasetImportJob untuk file: {$this->filePath}");
        $importService->import($this->filePath, $this->userId);
        Log::info("ProcessDatasetImportJob selesai.");
    }
}
