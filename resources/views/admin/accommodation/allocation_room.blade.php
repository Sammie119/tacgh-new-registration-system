@php
    use App\Enums\RolesEnum;
@endphp

@extends('layouts.app')

@section('title', 'TAC-GH | Allocate Room')

@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>{{ $venue->name }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">{{ $venue->name }}</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">{{ $venue->name }}</h5>
                                {{--                                <x-button--}}
                                {{--                                    type="button"--}}
                                {{--                                    icon="bi bi-plus-lg"--}}
                                {{--                                    class="btn-primary rounded-pill"--}}
                                {{--                                    title="Add New Record"--}}
                                {{--                                    name="New Resident"--}}
                                {{--                                    data-bs-size="" --}}{{--sizes: modal-sm, modal-lg, modal-xl, modal-fullscreen--}}
                                {{--                                    data-bs-toggle="modal"--}}
                                {{--                                    data-bs-target="#exampleModal"--}}
                                {{--                                    data-bs-title="Add New Resident"--}}
                                {{--                                    data-bs-url="/execute_form/view/accommodations/{{ $venue->id }}"--}}
                                {{--                                />--}}
                            </div>

                            <x-notify-error :messages="$errors->all()" />

                            <!-- Table with stripped rows -->
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th class="no-sort">#</th>
                                    <th>Name</th>
                                    <th>Blocks</th>
                                    <th>Rooms</th>
                                    <th>Gender</th>
                                    <th>Availability</th>
                                    <th>Enabled</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $total_blocks = 0;
                                    $total_rooms = 0;
                                @endphp
                                @forelse($accommodations as $key => $accommodation)
                                    <tr class="accommodation_{{ $accommodation->id }}">
                                        <td style="width: 50px">{{ ++$key }}</td>
                                        <td>{{ $accommodation->name }}</td>
                                        <td>{{ $accommodation->total_blocks }}</td>
                                        <td>{{ $accommodation->total_rooms }}</td>
                                        <td>{{ get_gender($accommodation->gender) }}</td>
                                        <td>{{ $accommodation->status }}</td>
                                        <td>{!! get_active_flag($accommodation->active_flag) !!}</td>
                                        <td style="width: 230px">
                                            <x-button
                                                type='button'
                                                class="btn-info btn-sm"
                                                icon="fas fa-angle-double-down"
                                                name="Setup Blocks"
                                                data-bs-toggle="modal"
                                                data-bs-target="#exampleModal"
                                                data-bs-title="Create Blocks in {{ $accommodation->name }}"
                                                data-bs-url="/execute_form/view/blocks_setup/{{ $accommodation->id }}"
                                                data-bs-size="modal-xl"
                                                title="Add Block"
                                                style="padding: 6px 10px 6px 10px"
                                            />
                                            @if(use_roles_sidebar(RolesEnum::SYSTEMDEVELOPER) || use_roles_sidebar(RolesEnum::SYSTEMADMIN) || use_roles_sidebar(RolesEnum::SUPERADMIN))
                                                <x-button
                                                    type='button'
                                                    class="btn-icon btn-primary btn-sm"
                                                    icon="bi bi-pencil-square"
                                                    name=""
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#exampleModal"
                                                    data-bs-title="Edit Resident"
                                                    data-bs-url="/execute_form/edit/resident/{{ $accommodation->id }}"
                                                    data-bs-size=""
                                                    title="Edit"
                                                />
                                            @endif
                                            @if(use_roles_sidebar(RolesEnum::SYSTEMDEVELOPER) || use_roles_sidebar(RolesEnum::SYSTEMADMIN))
                                                <x-button
                                                    type='button'
                                                    class="btn-icon btn-danger btn-sm"
                                                    icon="bi bi-trash-fill"
                                                    name=""
                                                    title="Delete"
                                                    onclick="deleteFunction(
                                                            {{ $accommodation->id }},
                                                            'accommodation',
                                                            '/execute_form/delete/accommodation/{{ $accommodation->id }}'
                                                        )"
                                                />
                                            @endif
                                        </td>
                                    </tr>
                                    @php
                                        $total_blocks += $accommodation->total_blocks;
                                        $total_rooms += $accommodation->total_rooms;
                                    @endphp
                                @empty
                                    <tr>
                                        <td colspan="50">No Data Found</td>
                                    </tr>
                                @endforelse
                                <tr>
                                    <th></th>
                                    <th>Total</th>
                                    <th>{{ $total_blocks }}</th>
                                    <th>{{ $total_rooms }}</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
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

    <x-modal-toggle />
@endsection


