@extends('layouts.app')

@section('title', 'TAC-GH | Outstanding Balances')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="Outstanding Balances" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Outstanding Balances</h5>
                            </div>

                            <x-notify-error :messages="$errors->all()" />

                            <form method="GET" action="{{ route('outstanding_balances') }}" class="row g-2 mb-3">
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
                                        <a href="{{ route('outstanding_balances') }}" class="btn btn-outline-secondary">Clear</a>
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
                                    <th>Phone</th>
                                    <th>Total to Pay</th>
                                    <th>Total Paid</th>
                                    <th>Balance Due</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($balances as $key => $registrant)
                                    @php
                                        $registrant_name = strtoupper(trim(($dropdown_names[$registrant->title] ?? '').' '.$registrant->first_name.' '.$registrant->other_names.' '.$registrant->surname));
                                        $totalToPay = $balance_totals[$registrant->id]->total_to_pay ?? 0;
                                        $totalPaid = $balance_totals[$registrant->id]->total_paid ?? 0;
                                        $balanceDue = $totalToPay - $totalPaid;
                                    @endphp
                                    <tr>
                                        <td style="width: 40px">{{ $balances->firstItem() + $key }}</td>
                                        <td>{{ $registrant_name }}</td>
                                        <td>{{ $registration_numbers[$registrant->id] ?? 'NULL' }}</td>
                                        <td>{{ $registrant->phone_number }}</td>
                                        <td>{{ number_format($totalToPay, 2) }}</td>
                                        <td>{{ number_format($totalPaid, 2) }}</td>
                                        <td>{{ number_format($balanceDue, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="50">No Data Found</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                            <!-- End Table with stripped rows -->

                            {{ $balances->links() }}

                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main><!-- End #main -->
@endsection
