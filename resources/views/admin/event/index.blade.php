@extends('layouts.app')

@section('title', 'TAC-GH | Events')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="Events" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Events</h5>
                                <x-button
                                    type="button"
                                    icon="bi bi-plus-lg"
                                    class="btn-primary rounded-pill"
                                    title="Add New Record"
                                    name="New Event"
                                    data-bs-size="modal-lg" {{--sizes: modal-sm, modal-lg, modal-xl, modal-fullscreen--}}
                                    data-bs-toggle="modal"
                                    data-bs-target="#exampleModal"
                                    data-bs-title="Add New Event"
                                    data-bs-url="/execute_form/create/event"
                                />
                            </div>

                            <x-notify-error :messages="$errors->all()" />

                            <!-- Table with stripped rows -->
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th>Event Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Venue</th>
                                    <th>Status</th>
                                    <th class="no-sort text-end">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($events as $event)
                                    <tr class="event_{{ $event->id }}">
                                        <td>
                                            <a href="#"
                                               data-bs-toggle="modal"
                                               data-bs-target="#exampleModal"
                                               data-bs-title="Event Details"
                                               data-bs-url="/execute_form/view/event_details/{{ $event->id }}"
                                               data-bs-size="modal-lg"
                                               title="View Details"
                                            >{{ $event->name }}</a>
                                        </td>
                                        <td>{{ $event->start_date }}</td>
                                        <td>{{ $event->end_date }}</td>
                                        <td>{{ $venue_names[$event->venue_id] ?? null }}</td>
                                        <td>{{ $event->status }}</td>
                                        <td class="text-nowrap text-end">
                                            @if((int) $event->id === (int) Auth::user()->event_id)
                                                <span class="badge bg-success" title="This is your currently active event">Active</span>
                                            @else
                                                <form method="POST" action="{{ route('event.switch') }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                                                    <x-button
                                                        type="submit"
                                                        class="btn-outline-secondary btn-sm"
                                                        icon="bi bi-arrow-repeat"
                                                        name="Switch"
                                                        title="Make this your active event"
                                                        style="padding: 6px 10px 6px 10px"
                                                    />
                                                </form>
                                            @endif
                                            <x-button
                                                type='button'
                                                class="btn-info btn-sm"
                                                icon="bi bi-chevron-double-down"
                                                name="Fees"
                                                data-bs-toggle="modal"
                                                data-bs-target="#exampleModal"
                                                data-bs-title="Create Fees for {{ $event->name }}"
                                                data-bs-url="/execute_form/view/fees/{{ $event->id }}"
                                                data-bs-size="modal-lg"
                                                title="Add Fees"
                                                style="padding: 6px 10px 6px 10px"
                                            />
                                            <x-button
                                                type='button'
                                                class="btn-warning btn-sm"
                                                icon="bi bi-percent"
                                                name="Promotions"
                                                data-bs-toggle="modal"
                                                data-bs-target="#exampleModal"
                                                data-bs-title="Manage Promotions for {{ $event->name }}"
                                                data-bs-url="/execute_form/view/promotions/{{ $event->id }}"
                                                data-bs-size="modal-xl"
                                                title="Manage Promotions"
                                                style="padding: 6px 10px 6px 10px"
                                            />
                                            <x-button
                                                type='button'
                                                class="btn-icon btn-primary btn-sm"
                                                icon="bi bi-pencil-square"
                                                name=""
                                                data-bs-toggle="modal"
                                                data-bs-target="#exampleModal"
                                                data-bs-title="Edit Event"
                                                data-bs-url="/execute_form/edit/event/{{ $event->id }}"
                                                data-bs-size="modal-lg"
                                                title="Edit"
                                            />
                                            <x-button
                                                type='button'
                                                class="btn-icon btn-danger btn-sm"
                                                icon="bi bi-trash-fill"
                                                name=""
                                                title="Delete"
                                                onclick="deleteFunction(
                                                    {{ $event->id }},
                                                    'event',
                                                    '/execute_form/delete/event/{{ $event->id }}'
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
