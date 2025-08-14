@extends('layouts.app')

@section('title', 'TAC-GH | Forms')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="Forms" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Forms</h5>
                                <x-button
                                    type="button"
                                    icon="bi bi-plus-lg"
                                    class="btn-primary rounded-pill"
                                    title="Add New Record"
                                    name="New Forms"
                                    data-bs-size="modal-lg" {{--sizes: modal-sm, modal-lg, modal-xl, modal-fullscreen--}}
                                    data-bs-toggle="modal"
                                    data-bs-target="#exampleModal"
                                    data-bs-title="Create Form"
                                    data-bs-url="/execute_form/create/forms"
                                />
                            </div>
{{--                            <a href="{{ route('forms.create') }}" class="btn btn-primary mb-3">Create Form</a>--}}

                            <x-notify-error :messages="$errors->all()" />

                            <!-- Table with stripped rows -->
                            <table class="table datatable">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th>Responses</th>
                                        <th>Public Link</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($forms as $key => $form)
                                        <tr class="forms_{{ $form->id }}">
                                            <td style="width: 40px">{{ ++$key }}</td>
                                            <td>{{ $form->title }}</td>
                                            <td>{{ $form->responses()->count() }}</td>
                                            <td><a href="{{ url('/forms/'.$form->slug) }}" target="_blank">{{ url('/forms/'.$form->slug) }}</a></td>
                                            <td>
                                                <a href="{{ route('forms.report', $form) }}" class="btn btn-sm btn-info">Report</a>
                                                <a href="{{ route('forms.export', $form) }}" class="btn btn-sm btn-success">Export Excel</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6">No forms yet</td></tr>
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

