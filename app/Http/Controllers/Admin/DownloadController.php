<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DownloadService;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    private DownloadService $downloadService;

    public function __construct(DownloadService $downloadService)
    {
        $this->downloadService = $downloadService;
    }

    public function index()
    {
        return $this->downloadService->index();
    }

    public function store(Request $request)
    {
        $request->validate([
            'file_name' => 'required|string|max:255',
            'file' => 'required|mimes:pdf|max:1024',
        ],[
            'file.mimes' => 'The file must be a PDF file.',
            'file.max' => 'The file size must not exceed 1MB.',
        ]);

        return $this->downloadService->store($request);
    }

    public function update(Request $request)
    {
        $request->validate([
            'file_name' => 'required|string|max:255',
            'file' => 'nullable|mimes:pdf|max:1024',
        ],[
            'file.mimes' => 'The file must be a PDF file.',
            'file.max' => 'The file size must not exceed 1MB.',
        ]);

        return $this->downloadService->update($request);
    }

    public function downloadFile($id)
    {
        return $this->downloadService->downloadFile($id);
    }

    static public function destroy($id)
    {
        return DownloadService::destroy($id);
    }
}
