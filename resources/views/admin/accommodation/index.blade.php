@extends('layouts.app')

@section('title', 'TAC-GH | Venues')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="Venues" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Venues</h5>
                                <x-button
                                    type="button"
                                    icon="bi bi-plus-lg"
                                    class="btn-primary rounded-pill"
                                    title="Add New Record"
                                    name="New Venue"
                                    data-bs-size="" {{--sizes: modal-sm, modal-lg, modal-xl, modal-fullscreen--}}
                                    data-bs-toggle="modal"
                                    data-bs-target="#exampleModal"
                                    data-bs-title="Add New Venue"
                                    data-bs-url="/execute_form/create/venue"
                                />
                            </div>

                            <x-notify-error :messages="$errors->all()" />

                            <!-- Table with stripped rows -->
                            <table class="table datatable">
                                <thead>
                                    <tr>
                                        <th class="no-sort">#</th>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Region</th>
                                        <th>Status</th>
                                        <th class="no-sort">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($venues as $key => $venue)
                                        <tr class="venue_{{ $venue->id }}">
                                            <td style="width: 50px">{{ ++$key }}</td>
                                            <td>{{ $venue->name }}</td>
                                            <td>{{ $venue->location }}</td>
                                            <td>{{ get_dropdown_name($venue->region_id) }}</td>
                                            <td>{!! get_active_flag($venue->active_flag) !!}</td>
                                            <td style="width: 230px">
                                                <a
                                                    class="btn rounded-pill btn-icon btn-primary btn-sm"
                                                    href="{{ route('accommodations', [$venue->id]) }}"
                                                    title="Resident Setup"
                                                >
                                                    <i class="bi bi-arrow-bar-down"></i>
                                                </a>
                                                <x-button
                                                    type='button'
                                                    class="btn-info btn-sm"
                                                    icon="fas fa-angle-double-down"
                                                    name="Residents"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#exampleModal"
                                                    data-bs-title="Create Residents in {{ $venue->name }}"
                                                    data-bs-url="/execute_form/view/accommodations/{{ $venue->id }}"
                                                    data-bs-size="modal-xl"
                                                    title="Add Resident"
                                                    style="padding: 6px 10px 6px 10px"
                                                />
                                                <x-button
                                                    type='button'
                                                    class="btn-icon btn-primary btn-sm"
                                                    icon="bi bi-pencil-square"
                                                    name=""
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#exampleModal"
                                                    data-bs-title="Edit Venue"
                                                    data-bs-url="/execute_form/edit/venue/{{ $venue->id }}"
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
                                                        {{ $venue->id }},
                                                        'venue',
                                                        '/execute_form/delete/venue/{{ $venue->id }}'
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
