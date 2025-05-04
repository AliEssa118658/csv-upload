<?php

// app/Http/Controllers/UploadController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Upload;
use App\Models\Product;
use App\Jobs\ProcessCsvUpload;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function showForm()
    {
        return view('upload.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv',
        ]);

        // Save the upload entry in the database
        $upload = Upload::create([
           'file_name' => $request->file('file')->getClientOriginalName(),
            'status' => 'pending',
        ]);

        // Store the file locally
        $path = $request->file('file')->storeAs('uploads', $upload->id . '.csv'); // relative path, correct
        // Dispatch the job to process the file in the background
        $processor = new \App\Jobs\ProcessCsvUpload($upload, $path);
        $processor->handle();

        return redirect()->back()->with('status', 'File uploaded successfully.');
    }
    public function status()
{
    $uploads = Upload::latest()->get();
    return response()->json($uploads);
}

    public function index()
    {
        $uploads = Upload::latest()->get();
        return response()->json($uploads);
    }

}
