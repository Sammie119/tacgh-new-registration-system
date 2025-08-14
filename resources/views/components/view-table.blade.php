@props(['table_data' => [], 'info' => [], 'image' => '', 'active' => 1])

@isset($image)
    <div class="d-flex justify-content-center">{!! $image !!}</div>
@endisset

<table class="table table-head-bg-primary mt-4">
    <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">Item</th>
        <th scope="col">Description</th>
    </tr>
    </thead>
    <tbody>
        @php
            $i = 1;
        @endphp
        @forelse($table_data as $key=> $data)
            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ $info[$key] }}</td>
                <td>{{ $data }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No Data</td>
            </tr>
        @endforelse
        <tr>
            <td>{{ $i }}</td>
            <td>Status</td>
            <td>{!! $active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' !!}</td>
        </tr>

    </tbody>
</table>
