<table class="table">
    <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col">File Name</th>
            <th scope="col">Downloads</th>
            <th scope="col">Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse(\App\Models\Admin\Download::where('event_id', $registrant->event_id)->get() as $key => $download)
            <tr>
                <td style="width: 50px">{{ ++$key }}</td>
                <td>{{ $download->file_name }}</td>
                <td style="width: 100px">{{ $download->download_count }}</td>
                <td style="width: 150px">
                    <a href="{{ route('registrant.download.file', $download->id) }}" class="btn btn-sm btn-primary"><i class="bi bi-download"></i> Download</a>
                </td>
            </tr>
        @empty
            <tr>
                <th colspan="5">No Data Found</th>
            </tr>
        @endforelse
    </tbody>
</table>
