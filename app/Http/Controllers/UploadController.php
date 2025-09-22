<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Jobs\ProcessBatch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class UploadController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $batches = Batch::latest()->paginate(20);
            return response()->json($batches);
        } catch (\Exception $e) {
            $batches = Batch::paginate(20);
            return response()->json($batches);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:json|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid input',
                'messages' => $validator->errors()
            ], 400);
        }

        try {
            $file = $request->file('file');
            $content = file_get_contents($file->getRealPath());
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid JSON format'
                ], 400);
            }

            $batch = Batch::create([
                'filename' => $file->getClientOriginalName(),
                'raw_data' => $data,
                'status' => 'pending',
                'created_at' => now(),
            ]);

            ProcessBatch::dispatch($batch);

            return response()->json([
                'message' => 'File uploaded successfully',
                'id' => $batch->id,
                'batch_id' => $batch->id,
                'status' => $batch->status
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to process file',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Batch $upload): JsonResponse
    {
        $upload->load([
            'timeTrackingEvents',
            'storeCheckins',
            'priceSurveys',
            'shelfLives',
            'stockAvailabilities',
            'media'
        ]);

        return response()->json([
            'batch' => $upload,
            'statistics' => [
                'time_tracking_events' => $upload->timeTrackingEvents()->count(),
                'store_checkins' => $upload->storeCheckins()->count(),
                'price_surveys' => $upload->priceSurveys()->count(),
                'shelf_lives' => $upload->shelfLives()->count(),
                'stock_availabilities' => $upload->stockAvailabilities()->count(),
                'media' => $upload->media()->count(),
            ]
        ]);
    }

    public function reprocess(Batch $upload): JsonResponse
    {
        try {
            $upload->update([
                'status' => 'pending',
                'error_message' => null,
                'processed_items' => 0,
                'failed_items' => 0,
                'started_at' => null,
                'completed_at' => null,
            ]);
            ProcessBatch::dispatch($upload);

            return response()->json([
                'message' => 'Batch reprocessing started',
                'batch_id' => $upload->id,
                'status' => $upload->status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to reprocess batch',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}