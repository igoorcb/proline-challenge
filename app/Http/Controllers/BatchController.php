<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Jobs\ProcessBatch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BatchController extends Controller
{
    public function index(): JsonResponse
    {
        $batches = Batch::latest()->paginate(20);

        return response()->json($batches);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid input',
                'messages' => $validator->errors()
            ], 400);
        }

        try {
            $data = json_decode($request->input('data'), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid JSON format'
                ], 400);
            }

            $batch = Batch::create([
                'raw_data' => $data,
                'status' => 'pending',
                'created_at' => now(),
            ]);

            ProcessBatch::dispatch($batch);

            return response()->json([
                'message' => 'Batch uploaded successfully',
                'batch_id' => $batch->id,
                'status' => $batch->status
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to process batch',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Batch $batch): JsonResponse
    {
        $batch->load([
            'timeTrackingEvents',
            'storeCheckins',
            'priceSurveys',
            'shelfLives',
            'stockAvailabilities',
            'media'
        ]);

        return response()->json([
            'batch' => $batch,
            'statistics' => [
                'time_tracking_events' => $batch->timeTrackingEvents()->count(),
                'store_checkins' => $batch->storeCheckins()->count(),
                'price_surveys' => $batch->priceSurveys()->count(),
                'shelf_lives' => $batch->shelfLives()->count(),
                'stock_availabilities' => $batch->stockAvailabilities()->count(),
                'media' => $batch->media()->count(),
            ]
        ]);
    }
}