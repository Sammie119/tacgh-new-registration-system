<?php

namespace App\Services\Admin;

use App\Helpers\Utils;
use App\Models\Admin\Download;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DownloadService
{
    public function index()
    {
        $data['downloads'] = Download::where('event_id', get_logged_in_user_event_id())->orderByDesc('id')->get();
        return view('admin.downloads.index', $data);
    }

    public function store($request)
    {
//        dd($request->all());
        $path = Utils::fileUpload($request, 'uploads');

        if(!$path){
            return redirect(route('downloads', absolute: false))->with('error', 'File Upload Unsuccessful!!!');
        }

        $upload = Download::firstOrCreate([
                'event_id' => get_logged_in_user_event_id(),
                'file_name' => $request->file_name,
            ],
            [
                'file_path' => $path,
                'download_count' => 0,
                'active_flag' => $request->active_flag ?? 0,
                'created_by' => get_logged_in_user_id(),
                'updated_by' => get_logged_in_user_id(),
            ]);

        if($upload){
            return redirect(route('downloads', absolute: false))->with('success', 'Download Created Successfully!!!');
        }

        return redirect(route('downloads', absolute: false))->with('error', 'Download Creation Unsuccessful!!!');
    }

    public function update($request)
    {
//        dd($request->all());
        $download = Download::find($request->id);
        $path = Utils::fileUpload($request, 'uploads', $download->file_path);

        $upload = $download->update([
            'file_name' => $request->file_name,
            'active_flag' => $request->active_flag ?? 0,
            'updated_by' => get_logged_in_user_id(),
        ]);

        if($path){
            $upload = $download->update(['file_path' => $path]);
        }

        if($upload){
            return redirect(route('downloads', absolute: false))->with('success', 'Download Updated Successfully!!!');
        }

        return redirect(route('downloads', absolute: false))->with('error', 'Download Updat Unsuccessful!!!');
    }

    public function downloadFile($id)
    {
        $file = Download::find($id);
        $filename = 'app/'.$file->file_path;
//        dd($filename);
        if (!File::exists(storage_path($filename))) {
            return redirect(route('registrant_page', absolute: false))->with('error', 'File Not Found!!!');
        }
        $file->increment('download_count');
        return Storage::disk('local')->download($file->file_path);
//        return Storage::download($filename);
    }

    static public function destroy($id)
    {
        $record = Download::find($id);
        if($record){
            $file = 'app/'.$record->file_path;
            if (File::exists(storage_path($file))) {
                File::delete(storage_path($file));
            }
            $record->delete();
            return 1;
        }
        return 0;
    }
}
