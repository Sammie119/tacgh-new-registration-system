@extends('layouts.app')

@section('title', 'TAC-GH | All Registrants')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="All Registrants" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">All Registrants</h5>
                            </div>

                            <x-notify-error :messages="$errors->all()" />
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th class="no-sort">#</th>
                                    <th>Name</th>
                                    <th>Reg. #</th>
                                    <th>gender</th>
                                    <th>phone_number</th>
                                    <th>Room</th>
                                    <th>ChkIn</th>
                                    <th>ChkOut</th>
                                    <th>ChckIn By</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($registrants as $key => $registrant)
                                    @php
                                        $confirmed_registrant = \App\Models\Registrant::where('stage_id', $registrant->id)->first();
                                    @endphp

                                    <tr class="venue_{{ $registrant->id }}">
                                        <td style="width: 50px">{{ ++$key }}</td>
                                        <td>{{ event_registrant_name($registrant->id)  }}</td>
                                        <td>{{ $confirmed_registrant->registration_no ?? 'NULL' }}</td>
                                        <td>{{ get_dropdown_name($registrant->gender) }}</td>
                                        <td>{{ $registrant->phone_number }}</td>
                                        <td>{{ get_room_number($confirmed_registrant->room_no) }}</td>
                                        <td>{{ $confirmed_registrant->check_in }}</td>
                                        <td>{{ $confirmed_registrant->check_out }}</td>
                                        <td>{{ get_user_name($confirmed_registrant->check_in_by) }}</td>
                                        <td>
                                            <x-button
                                                type='button'
                                                class="btn-icon btn-primary btn-sm"
                                                icon="bi bi-hand-thumbs-up-fill"
                                                name=""
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmation"
                                                title="Edit"
                                                onclick="window.location.href='{{ route('check_in', $registrant->id) }}'"
                                            /> <br>
                                            <x-button
                                                type='button'
                                                class="btn-icon btn-danger btn-sm mt-1"
                                                icon="bi bi-hand-thumbs-down-fill"
                                                name=""
                                                title="Delete"
                                                onclick="window.location.href='{{ route('check_out', $registrant->id) }}'"
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

                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main><!-- End #main -->

    <x-modal />
@endsection


