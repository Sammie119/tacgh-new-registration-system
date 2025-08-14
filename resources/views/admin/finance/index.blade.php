@extends('layouts.app')

@section('title', 'TAC-GH | Finances')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="Finances" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Finances</h5>
{{--                                <x-button--}}
{{--                                    type="button"--}}
{{--                                    icon="bi bi-plus-lg"--}}
{{--                                    class="btn-primary rounded-pill"--}}
{{--                                    title="Add New Record"--}}
{{--                                    name="New Event"--}}
{{--                                    data-bs-size="modal-lg" --}}{{--sizes: modal-sm, modal-lg, modal-xl, modal-fullscreen--}}
{{--                                    data-bs-toggle="modal"--}}
{{--                                    data-bs-target="#exampleModal"--}}
{{--                                    data-bs-title="Add New Event"--}}
{{--                                    data-bs-url="/execute_form/create/event"--}}
{{--                                />--}}
                            </div>

                            <x-notify-error :messages="$errors->all()" />

                            <!-- Table with stripped rows -->
                            <table class="table datatable">
                                <thead>
                                <tr>
                                    <th class="no-sort">#</th>
                                    <th>Name</th>
                                    <th>Reg No.</th>
                                    <th>Total Fees</th>
                                    <th>Paid</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($finances as $key => $finance)
                                    @php
                                        $amount_paid = \App\Models\Admin\OnlinePayment::where([
                                                'reg_id' => $finance->reg_id,
                                                'event_id' => $finance->event_id
                                            ])->sum('amount_paid');
                                    @endphp
                                    <tr class="event_{{ $finance->id }}">
                                        <td style="width: 40px">{{ ++$key }}</td>
                                        <td>{{ event_registrant_name($finance->reg_id) }}</td>
                                        <td>{{ $finance->registrant->registration_no }}</td>
                                        <td>{{ $finance->amount_to_pay }}</td>
                                        <td>{{ $finance->amount_paid }}</td>
                                        <td>{{ ($finance->payment_status) ? 'Successful' : 'Failed' }}</td>
                                        <td>{{ $finance->date_paid }}</td>
                                        <td style="width: 50px">
                                            @if($amount_paid >= $finance->amount_to_pay)
                                                <x-button
                                                    type='button'
                                                    class="btn-info btn-sm"
                                                    icon="fas fa-angle-double-down"
                                                    name="Approve"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#exampleModal"
                                                    data-bs-title="Clearance"
                                                    data-bs-url="/execute_form/view/financial_clearance/{{ $finance->id }}"
                                                    data-bs-size=""
                                                    title="Approve"
                                                    style="padding: 6px 10px 6px 10px"
                                                    disabled
                                                />
                                            @else
                                                <x-button
                                                    type='button'
                                                    class="btn-info btn-sm"
                                                    icon="fas fa-angle-double-down"
                                                    name="Approve"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#exampleModal"
                                                    data-bs-title="Clearance"
                                                    data-bs-url="/execute_form/view/financial_clearance/{{ $finance->id }}"
                                                    data-bs-size=""
                                                    title="Approve"
                                                    style="padding: 6px 10px 6px 10px"
                                                />
                                            @endif
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

