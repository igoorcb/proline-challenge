<?php

namespace App\Jobs;

use App\Models\Batch;
use App\Models\StoreCheckin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessStoreCheckins implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120];

    public function __construct(
        public Batch $batch,
        public array $checkins
    ) {}

    public function handle(): void
    {
        Log::info('Processing StoreCheckins', [
            'batch_id' => $this->batch->id,
            'count' => count($this->checkins)
        ]);

        $processed = 0;
        $failed = 0;

        foreach ($this->checkins as $checkin) {
            try {
                StoreCheckin::create([
                    'batch_id' => $this->batch->id,
                    'store_id' => $checkin['storeId'] ?? null,
                    'user_id' => $checkin['userId'] ?? null,
                    'latitude' => $checkin['latitude'] ?? null,
                    'longitude' => $checkin['longitude'] ?? null,
                    'battery_level' => $checkin['batteryLevel'] ?? null,
                    'checkin_at' => $checkin['created_at'] ?? null,
                ]);
                $processed++;
            } catch (\Exception $e) {
                $failed++;
                Log::error('Failed to process StoreCheckin', [
                    'batch_id' => $this->batch->id,
                    'checkin' => $checkin,
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
        }
    }
}