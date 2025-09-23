<?php

namespace App\Jobs;

use App\Models\Batch;
use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120];

    public function __construct(
        public Batch $batch,
        public array $mediaData,
        public string $type
    ) {}

    public function handle(): void
    {
        Log::info('Processing Media', [
            'batch_id' => $this->batch->id,
            'type' => $this->type,
            'count' => count($this->mediaData)
        ]);

        $processed = 0;
        $failed = 0;

        foreach ($this->mediaData as $data) {
            try {
                Media::create([
                    'batch_id' => $this->batch->id,
                    'type' => $this->type,
                    'operation_name' => $data['operationName'] ?? null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'battery_level' => $data['batteryLevel'] ?? null,
                    'image' => $data['image'] ?? null,
                    'status' => $data['status'] ?? null,
                    'file_path' => $data['file_path'] ?? null,
                    'file_size' => $data['file_size'] ?? null,
                    'media_created_at' => $data['created_at'] ?? null,
                ]);
                $processed++;
            } catch (\Exception $e) {
                $failed++;
                Log::error('Failed to process Media', [
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
