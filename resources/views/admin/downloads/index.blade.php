@extends('layouts.app')

@section('title', 'TAC-GH | Downloads')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="Downloads" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Downloads</h5>
                                <x-button
                                    type="button"
                                    icon="bi bi-plus-lg"
                                    class="btn-primary rounded-pill"
                                    title="Add New Record"
                                    name="New Download Document"
                                    data-bs-size="" {{--sizes: modal-sm, modal-lg, modal-xl, modal-fullscreen--}}
                                    data-bs-toggle="modal"
                                    data-bs-target="#exampleModal"
                                    data-bs-title="Add New Event"
                                    data-bs-url="/execute_form/create/downloads"
                                />
                            </div>

                            <x-notify-error :messages="$errors->all()" />

                            <!-- Table with stripped rows -->
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th class="no-sort">#</th>
                                    <th>File Name</th>
                                    <th>File URL</th>
                                    <th>Downloads</th>
                                    <th>Enabled?</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($downloads as $key => $download)
                                    <tr class="download_{{ $download->id }}">
                                        <td style="width: 40px">{{ ++$key }}</td>
                                        <td>{{ $download->file_name }}</td>
                                        <td>{{ $download->file_path }}</td>
                                        <td>{{ $download->download_count }}</td>
                                        <td>{!! get_active_flag($download->active_flag) !!}</td>
                                        <td style="width: 100px">
                                            <x-button
                                                type='button'
                                                class="btn-icon btn-primary btn-sm"
                                                icon="bi bi-pencil-square"
                                                name=""
                                                data-bs-toggle="modal"
                                                data-bs-target="#exampleModal"
                                                data-bs-title="Edit Event"
                                                data-bs-url="/execute_form/edit/downloads/{{ $download->id }}"
                                                data-bs-size=""
                                                title="Edit"
                                            />
                                            <x-button
                                                type='button'
                                                class="btn-icon btn-danger btn-sm"
                                                icon="bi bi-trash-fill"
                                                name=""
                                                title="Delete"
                                                onclick="deleteFunction(
                                                    {{ $download->id }},
                                                    'download',
                                                    '/execute_form/delete/downloads/{{ $download->id }}',
                                                    'refresh'
                                                )"
                                            />
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="50">No Data Found</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                            <!-- End Table with stripped rows -->

                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main><!-- End #main -->

    <x-modal />
@endsection
