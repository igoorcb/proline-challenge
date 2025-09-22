<?php

namespace App\Jobs;

use App\Models\Batch;
use App\Models\TimeTrackingEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessTimeTrackingEvents implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120];

    public function __construct(
        public Batch $batch,
        public array $events
    ) {}

    public function handle(): void
    {
        Log::info('Processing TimeTrackingEvents', [
            'batch_id' => $this->batch->id,
            'count' => count($this->events)
        ]);

        $processed = 0;
        $failed = 0;

        foreach ($this->events as $event) {
            try {
                TimeTrackingEvent::create([
                    'batch_id' => $this->batch->id,
                    'type' => $event['type'] ?? null,
                    'latitude' => $event['latitude'] ?? null,
                    'longitude' => $event['longitude'] ?? null,
                    'battery_level' => $event['batteryLevel'] ?? null,
                    'event_created_at' => $event['created_at'] ?? null,
                ]);
                $processed++;
            } catch (\Exception $e) {
                $failed++;
                Log::error('Failed to process TimeTrackingEvent', [
                    'batch_id' => $this->batch->id,
                    'event' => $event,
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