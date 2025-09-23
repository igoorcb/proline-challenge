<?php

namespace App\Jobs;

use App\Models\Batch;
use App\Models\PriceSurvey;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPriceSurveys implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120];

    public function __construct(
        public Batch $batch,
        public array $surveys,
        public string $type
    ) {}

    public function handle(): void
    {
        Log::info('Processing PriceSurveys', [
            'batch_id' => $this->batch->id,
            'type' => $this->type,
            'count' => count($this->surveys)
        ]);

        $processed = 0;
        $failed = 0;

        foreach ($this->surveys as $survey) {
            try {
                PriceSurvey::create([
                    'batch_id' => $this->batch->id,
                    'type' => $this->type,
                    'survey_id' => $survey['survey_id'] ?? null,
                    'store_id' => $survey['store_id'] ?? null,
                    'user_id' => $survey['user_id'] ?? null,
                    'product_id' => $survey['product_id'] ?? null,
                    'product_category' => $survey['product_category'] ?? null,
                    'price' => $survey['price'] ?? null,
                    'currency' => $survey['currency'] ?? null,
                    'started_at' => $survey['started_at'] ?? null,
                    'registered_at' => $survey['registered_at'] ?? null,
                    'finished_at' => $survey['finished_at'] ?? null,
                ]);
                $processed++;
            } catch (\Exception $e) {
                $failed++;
                Log::error('Failed to process PriceSurvey', [
                    'batch_id' => $this->batch->id,
                    'type' => $this->type,
                    'survey' => $survey,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->batch->increment('processed_items', $processed);
        $this->batch->increment('failed_items', $failed);

        $this->checkBatchCompletion();
    }

    private function checkBatchCompletion(): void
    {
        $this->batch->refresh();

        if ($this->batch->processed_items + $this->batch->failed_items >= $this->batch->total_items) {
            $status = $this->batch->failed_items > 0 ? 'error' : 'completed';
            $this->batch->update([
                'status' => $status,
                'completed_at' => now(),
            ]);

            Log::info('Batch processing completed', [
                'batch_id' => $this->batch->id,
                'status' => $status,
                'processed' => $this->batch->processed_items,
                'failed' => $this->batch->failed_items
            ]);
        }
    }
}