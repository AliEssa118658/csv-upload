<?php

// app/Jobs/ProcessCsvUpload.php
namespace App\Jobs;

use App\Models\Upload;
use App\Models\Product;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Events\UploadStatusUpdated;

class ProcessCsvUpload implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Batchable;

    protected $upload;
    protected $path;

    public function __construct(Upload $upload, $path)
    {
        $this->upload = $upload;
        $this->path = $path;
    }

    public function handle()
    {
        try {
            // Update the status to 'processing'
            $this->upload->update(['status' => 'processing']);

            // Read the CSV file
            $file = Storage::path($this->path);
            $data = array_map('str_getcsv', file($file));
            // Process CSV data
            DB::beginTransaction();
            foreach ($data as $row) {
                // Clean data and upsert
                $cleanedRow = $this->cleanData($row);
                Product::updateOrCreate(
                    ['unique_key' => $cleanedRow['unique_key']],
                    $cleanedRow
                );
            }
            DB::commit();

            // Update the status to 'completed'
            $this->upload->update(['status' => 'completed']);
            // UploadStatusUpdated::dispatch('Processing complete', $this->upload->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CSV processing failed: ' . $e->getMessage());
            $this->upload->update(['status' => 'failed']);
        }
    }

    private function cleanData($row)
    {
        $clean = array_map(function ($field) {
            return mb_convert_encoding($field, 'UTF-8', 'UTF-8');
        }, $row);

        return [
            'unique_key' => $clean[0],
            'product_title' => $clean[1],
            'product_description' => $clean[2],
            'style' => $clean[3],
            'sanmar_mainframe_color' => $clean[4],
            'size' => $clean[5],
            'color_name' => $clean[6],
            'piece_price' => (float)$clean[7],
        ];
    }

}
