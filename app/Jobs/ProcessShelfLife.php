<?php

namespace App\Jobs;

use App\Models\Batch;
use App\Models\ShelfLife;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessShelfLife implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120];

    public function __construct(
        public Batch $batch,
        public array $shelfLifeData,
        public string $type
    ) {}

    public function handle(): void
    {
        Log::info('Processing ShelfLife', [
            'batch_id' => $this->batch->id,
            'type' => $this->type,
            'count' => count($this->shelfLifeData)
        ]);

        $processed = 0;
        $failed = 0;

        foreach ($this->shelfLifeData as $data) {
            try {
                ShelfLife::create([
                    'batch_id' => $this->batch->id,
                    'type' => $this->type,
                    'operation_id' => $data['operation_id'] ?? null,
                    'store_id' => $data['store_id'] ?? null,
                    'user_id' => $data['user_id'] ?? null,
                    'product_id' => $data['product_id'] ?? null,
                    'product_name' => $data['product_name'] ?? null,
                    'expiry_date' => $data['expiry_date'] ?? null,
                    'days_to_expire' => $data['days_to_expire'] ?? null,
                    'condition' => $data['condition'] ?? null,
                    'started_at' => $data['started_at'] ?? null,
                    'registered_at' => $data['registered_at'] ?? null,
                    'finished_at' => $data['finished_at'] ?? null,
                ]);
                $processed++;
            } catch (\Exception $e) {
                $failed++;
                Log::error('Failed to process ShelfLife', [
                    'batch_id' => $this->batch->id,
                    'type' => $this->type,
                    'data' => $data,
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
