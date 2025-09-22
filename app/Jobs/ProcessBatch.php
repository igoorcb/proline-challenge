<?php

namespace App\Jobs;

use App\Models\Batch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 120, 240];

    public function __construct(
        public Batch $batch
    ) {}

    public function handle(): void
    {
        Log::info('Processing batch', ['batch_id' => $this->batch->id]);

        $this->batch->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $data = $this->batch->raw_data;
            $totalItems = 0;

            if (isset($data['TimeTrackingEvents']) && is_array($data['TimeTrackingEvents'])) {
                $totalItems += count($data['TimeTrackingEvents']);
                ProcessTimeTrackingEvents::dispatch($this->batch, $data['TimeTrackingEvents']);
            }

            if (isset($data['StoreCheckins']) && is_array($data['StoreCheckins'])) {
                $totalItems += count($data['StoreCheckins']);
                ProcessStoreCheckins::dispatch($this->batch, $data['StoreCheckins']);
            }

            if (isset($data['PriceSurveyStart']) && is_array($data['PriceSurveyStart'])) {
                $totalItems += count($data['PriceSurveyStart']);
                ProcessPriceSurveys::dispatch($this->batch, $data['PriceSurveyStart'], 'Start');
            }

            if (isset($data['PriceSurveyRegister']) && is_array($data['PriceSurveyRegister'])) {
                $totalItems += count($data['PriceSurveyRegister']);
                ProcessPriceSurveys::dispatch($this->batch, $data['PriceSurveyRegister'], 'Register');
            }

            if (isset($data['PriceSurveyFinish']) && is_array($data['PriceSurveyFinish'])) {
                $totalItems += count($data['PriceSurveyFinish']);
                ProcessPriceSurveys::dispatch($this->batch, $data['PriceSurveyFinish'], 'Finish');
            }

            if (isset($data['ShelfLifeStart']) && is_array($data['ShelfLifeStart'])) {
                $totalItems += count($data['ShelfLifeStart']);
                ProcessShelfLife::dispatch($this->batch, $data['ShelfLifeStart'], 'Start');
            }

            if (isset($data['ShelfLifeRegister']) && is_array($data['ShelfLifeRegister'])) {
                $totalItems += count($data['ShelfLifeRegister']);
                ProcessShelfLife::dispatch($this->batch, $data['ShelfLifeRegister'], 'Register');
            }

            if (isset($data['ShelfLifeFinish']) && is_array($data['ShelfLifeFinish'])) {
                $totalItems += count($data['ShelfLifeFinish']);
                ProcessShelfLife::dispatch($this->batch, $data['ShelfLifeFinish'], 'Finish');
            }

            if (isset($data['StockAvailabilityStart']) && is_array($data['StockAvailabilityStart'])) {
                $totalItems += count($data['StockAvailabilityStart']);
                ProcessStockAvailability::dispatch($this->batch, $data['StockAvailabilityStart'], 'Start');
            }

            if (isset($data['StockAvailabilityRegister']) && is_array($data['StockAvailabilityRegister'])) {
                $totalItems += count($data['StockAvailabilityRegister']);
                ProcessStockAvailability::dispatch($this->batch, $data['StockAvailabilityRegister'], 'Register');
            }

            if (isset($data['StockAvailabilityFinish']) && is_array($data['StockAvailabilityFinish'])) {
                $totalItems += count($data['StockAvailabilityFinish']);
                ProcessStockAvailability::dispatch($this->batch, $data['StockAvailabilityFinish'], 'Finish');
            }

            if (isset($data['MediaStart']) && is_array($data['MediaStart'])) {
                $totalItems += count($data['MediaStart']);
                ProcessMedia::dispatch($this->batch, $data['MediaStart'], 'Start');
            }

            if (isset($data['MediaBuffer']) && is_array($data['MediaBuffer'])) {
                $totalItems += count($data['MediaBuffer']);
                ProcessMedia::dispatch($this->batch, $data['MediaBuffer'], 'Buffer');
            }

            if (isset($data['MediaFinish']) && is_array($data['MediaFinish'])) {
                $totalItems += count($data['MediaFinish']);
                ProcessMedia::dispatch($this->batch, $data['MediaFinish'], 'Finish');
            }

            $this->batch->update(['total_items' => $totalItems]);

            Log::info('Batch processing dispatched', [
                'batch_id' => $this->batch->id,
                'total_items' => $totalItems
            ]);

        } catch (\Exception $e) {
            Log::error('Batch processing failed', [
                'batch_id' => $this->batch->id,
                'error' => $e->getMessage()
            ]);

            $this->batch->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Batch job failed permanently', [
            'batch_id' => $this->batch->id,
            'error' => $exception->getMessage()
        ]);

        $this->batch->update([
            'status' => 'error',
            'error_message' => $exception->getMessage(),
            'completed_at' => now(),
        ]);
    }
}