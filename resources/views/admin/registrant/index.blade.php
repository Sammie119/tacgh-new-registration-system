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

                            <form method="GET" action="{{ route('all_registrant') }}" class="row g-2 mb-3">
                                <div class="col-auto">
                                    <input
                                        type="text"
                                        name="search"
                                        class="form-control"
                                        placeholder="Search by name, phone or reg. #"
                                        value="{{ $search }}"
                                    />
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                    @if($search)
                                        <a href="{{ route('all_registrant') }}" class="btn btn-outline-secondary">Clear</a>
                                    @endif
                                </div>
                            </form>

                            <table class="table">
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
                                        $confirmed_registrant = $registrant->stage;
                                        $registrant_name = strtoupper(trim(($dropdown_names[$registrant->title] ?? '').' '.$registrant->first_name.' '.$registrant->other_names.' '.$registrant->surname));
                                    @endphp

                                    <tr class="venue_{{ $registrant->id }}">
                                        <td style="width: 50px">{{ $registrants->firstItem() + $key }}</td>
                                        <td>{{ $registrant_name }}</td>
                                        <td>{{ $confirmed_registrant->registration_no ?? 'NULL' }}</td>
                                        <td>{{ $dropdown_names[$registrant->gender] ?? null }}</td>
                                        <td>{{ $registrant->phone_number }}</td>
                                        <td>{{ $room_names[$confirmed_registrant->room_no ?? null] ?? null }}</td>
                                        <td>{{ $confirmed_registrant->check_in ?? null }}</td>
                                        <td>{{ $confirmed_registrant->check_out ?? null }}</td>
                                        <td>{{ $check_in_by_names[$confirmed_registrant->check_in_by ?? null] ?? null }}</td>
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

                            {{ $registrants->links() }}

                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main><!-- End #main -->

    <x-modal />
@endsection


