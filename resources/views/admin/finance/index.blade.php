@php
    use App\Enums\RolesEnum;
@endphp

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
                                @if(use_roles_sidebar(RolesEnum::SYSTEMADMIN) || use_roles_sidebar(RolesEnum::FINANCE) || use_roles_sidebar(RolesEnum::SUPERADMIN))
                                    <x-button
                                        type="button"
                                        icon="bi bi-plus-lg"
                                        class="btn-primary rounded-pill"
                                        title="Add Online Payment"
                                        name="Online Payment"
                                        data-bs-size="modal-lg" {{--sizes: modal-sm, modal-lg, modal-xl, modal-fullscreen--}}
                                        data-bs-toggle="modal"
                                        data-bs-target="#exampleModal"
                                        data-bs-title="Add Online Payment"
                                        data-bs-url="/execute_form/create/online_payment_correction"
                                    />
                                @endif
                            </div>

                            <x-notify-error :messages="$errors->all()" />

                            <form method="GET" action="{{ route('payments') }}" class="row g-2 mb-3">
                                <div class="col-auto">
                                    <input
                                        type="text"
                                        name="search"
                                        class="form-control"
                                        placeholder="Search by name or reg. #"
                                        value="{{ $search }}"
                                    />
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                    @if($search)
                                        <a href="{{ route('payments') }}" class="btn btn-outline-secondary">Clear</a>
                                    @endif
                                </div>
                            </form>

                            <!-- Table with stripped rows -->
                            <table class="table">
                                <thead>
                                <tr>
                                    <th class="no-sort">#</th>
                                    <th>Name</th>
                                    <th>Reg No.</th>
                                    <th>Total Fees</th>
                                    <th>Paid</th>
                                    <th>Approved</th>
                                    <th class="no-sort">Payments</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($finances as $key => $finance)
                                    @php
                                        $total = $totals[$finance->id] ?? null;
                                        $amountToPay = $total->amount_to_pay ?? 0;
                                        $amountPaid = $total->amount_paid ?? 0;
                                        $approved = (int) ($total->approved ?? 1);
                                    @endphp
                                    <tr class="registrant_{{ $finance->id }}">
                                        <td style="width: 40px">{{ $finances->firstItem() + $key }}</td>
                                        <td>{{ strtoupper(trim(($dropdown_names[$finance->title] ?? '').' '.$finance->first_name.' '.$finance->other_names.' '.$finance->surname)) }}</td>
                                        <td>{{ $registration_numbers[$finance->id] ?? '' }}</td>
                                        <td>{{ number_format($amountToPay, 2) }}</td>
                                        <td>{{ number_format($amountPaid, 2) }}</td>
                                        <td style="width: 150px">
                                            <select class="form-select form-select-sm"
                                                    data-payment-id="{{ $total->latest_payment_id ?? '' }}"
                                                    onchange="openClearanceModal(this)">
                                                <option value="1" @if($approved !== 2) selected @endif>Disapproved</option>
                                                <option value="2" @if($approved === 2) selected @endif>Approved</option>
                                            </select>
                                        </td>
                                        <td style="width: 110px">
                                            <x-button
                                                type='button'
                                                class="btn-info btn-sm"
                                                icon="bi bi-clock-history"
                                                name="View"
                                                data-bs-toggle="modal"
                                                data-bs-target="#exampleModal"
                                                data-bs-title="Payment History"
                                                data-bs-url="/execute_form/view/payment_history/{{ $finance->id }}"
                                                data-bs-size=""
                                                title="View Payments"
                                                style="padding: 6px 10px 6px 10px"
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

                            {{ $finances->links() }}

                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Hidden trigger the Approved select uses to open the same
             clearance modal the old Approve button used, carrying which
             value (Approved/Disapproved) was picked via the URL. -->
        <button id="clearanceModalTrigger" type="button" style="display:none"
                data-bs-toggle="modal" data-bs-target="#exampleModal"></button>

    </main><!-- End #main -->

    <x-modal />

    <script>
        function openClearanceModal(select) {
            const paymentId = select.getAttribute('data-payment-id');
            const approved = select.value;
            const trigger = document.getElementById('clearanceModalTrigger');
            trigger.setAttribute('data-bs-url', `/execute_form/view/financial_clearance/${paymentId}?approved=${approved}`);
            trigger.setAttribute('data-bs-title', 'Clearance');
            trigger.click();
        }
    </script>
@endsection

