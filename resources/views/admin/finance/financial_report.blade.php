@extends('layouts.app')

@section('title', 'TAC-GH | Financial Report')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="Financial Report" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title">Financial Report</h5>
                                <div class="d-flex">
                                    <form>
                                        <input type="hidden" name="report" value="generate_report">
                                        <x-button
                                            type="button"
                                            icon="bi bi-arrow-clockwise"
                                            class="btn-primary rounded-pill"
                                            title="Add New Record"
                                            name="Generate Report"
                                            type="submit"
                                        />
                                    </form>
                                    <x-button
                                        type="button"
                                        icon="bi bi-x-lg"
                                        class="btn-danger rounded-pill"
                                        title="Add New Record"
                                        name="Clear"
                                        style="margin-left: 5px"
                                        onclick="window.location.href='{{ route('financial_report') }}'"
                                    />
                                </div>

                            </div>

                            <x-notify-error :messages="$errors->all()" />

                            <div class="card">
                                <div class="card-body pt-3">

                                    <!-- Bordered Tabs Justified -->
                                    <ul class="nav nav-tabs nav-tabs-bordered d-flex" id="borderedTabJustified" role="tablist">
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link w-100 active" id="home-tab" data-bs-toggle="tab" data-bs-target="#bordered-justified-home" type="button" role="tab" aria-controls="home" aria-selected="true">Summary</button>
                                        </li>
                                        <li class="nav-item flex-fill" role="presentation">
                                            <button class="nav-link w-100" id="profile-tab" data-bs-toggle="tab" data-bs-target="#bordered-justified-profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Detailed</button>
                                        </li>
                                    </ul>
                                    <div class="tab-content pt-2" id="borderedTabJustifiedContent">
                                        <div class="tab-pane fade show active" id="bordered-justified-home" role="tabpanel" aria-labelledby="home-tab">
                                            <h5 class="card-title">Summarized Report</h5>
                                            <!-- Table with stripped rows -->
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>TRANSACTION TYPE</th>
                                                        <th style="text-align: right">AMOUNT (GH₵)</th>
                                                        <th style="text-align: right">TOTAL (GH₵)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(!empty($report))
                                                        <tr>
                                                            <th>INCOME</th>
                                                            <th></th>
                                                            <th></th>
                                                        </tr>
                                                        <tr>
                                                            <th style="padding-left: 20px;">ONLINE PAYMENT</th>
                                                            <th style="text-align: right">{{ number_format($online_payments->sum('amount_paid'), 2) }}</th>
                                                            <th></th>
                                                        </tr>
                                                        <tr>
                                                            <th style="padding-left: 20px;">INCOME</th>
                                                            <th style="text-align: right">{{ number_format($finance_income->sum('amount'), 2) }}</th>
                                                            <th></th>
                                                        </tr>
                                                        <tr>
                                                            <?php $income = $finance_income->sum('amount') + $online_payments->sum('amount_paid'); ?>
                                                            <th style="padding-left: 20px;">TOTAL INCOME</th>
                                                            <th></th>
                                                            <th style="text-align: right">{{ number_format($income, 2) }}</th>
                                                        </tr>
                                                        <tr>
                                                            <th>EXPENSES</th>
                                                            <th></th>
                                                            <th></th>
                                                        </tr>
                                                        <tr>
                                                            <th style="padding-left: 20px;">TOTAL EXPENSES</th>
                                                            <th></th>
                                                            <th style="text-align: right">{{ number_format($finance_expense->sum('amount'), 2) }}</th>
                                                        </tr>
                                                        <tr>
                                                            <?php $amount = $income - $finance_expense->sum('amount'); ?>
                                                            <th>SURPLUS OR DEFICIT (INCOME less EXPENSES):</th>
                                                            <th></th>
                                                            <th style="text-align: right">{{ ($amount) ? number_format($amount,2) : "(".number_format($amount,2).")" }}</th>
                                                        </tr>
                                                    @else
                                                        <tr>
                                                            <td colspan="4">No Data Found</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                            <!-- End Table with stripped rows -->
                                        </div>
                                        <div class="tab-pane fade" id="bordered-justified-profile" role="tabpanel" aria-labelledby="profile-tab">
                                            <h5 class="card-title">Detailed Report</h5>
                                            <!-- Table with stripped rows -->
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>TRANSACTION TYPE</th>
                                                        <th style="text-align: right">AMOUNT (GH₵)</th>
                                                        <th style="text-align: right">TOTAL (GH₵)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(!empty($report))
                                                        <tr>
                                                            <th>INCOME</th>
                                                            <th></th>
                                                            <th></th>
                                                        </tr>
                                                        @foreach($online_payments as $online)
                                                            <tr>
                                                                <td style="padding-left: 20px;">Online Payment - {{ event_registrant_name($online->reg_id) }}</td>
                                                                <td style="text-align: right">{{ number_format($online->amount_paid, 2) }}</td>
                                                                <td></td>
                                                            </tr>
                                                        @endforeach
                                                        @foreach($finance_income as $f_income)
                                                            <tr>
                                                                <td style="padding-left: 20px;">{{ ($f_income->transaction_type == 'Others') ? $f_income->description : get_dropdown_name($f_income->transaction_type) }}</td>
                                                                <td style="text-align: right">{{ number_format($f_income->amount, 2) }}</td>
                                                                <td></td>
                                                            </tr>
                                                        @endforeach
                                                        <tr>
                                                            <th style="padding-left: 20px;">TOTAL INCOME</th>
                                                            <th></th>
                                                            <th style="text-align: right">{{ number_format($income, 2) }}</th>
                                                        </tr>
                                                        <tr>
                                                            <th>EXPENSES</th>
                                                            <th></th>
                                                            <th></th>
                                                        </tr>
                                                        @foreach($finance_expense as $expense)
                                                            <tr>
                                                                <td style="padding-left: 20px;">{{ ($expense->transaction_type == 'Others') ? $expense->description : get_dropdown_name($expense->transaction_type) }}</td>
                                                                <td style="text-align: right">{{ number_format($expense->amount, 2) }}</td>
                                                                <td></td>
                                                            </tr>
                                                        @endforeach
                                                        <tr>
                                                            <th style="padding-left: 20px;">TOTAL EXPENSES</th>
                                                            <th></th>
                                                            <th style="text-align: right">{{ number_format($finance_expense->sum('amount'), 2) }}</th>
                                                        </tr>
                                                        <tr>
                                                            <th>SURPLUS OR DEFICIT (INCOME less EXPENSES):</th>
                                                            <th></th>
                                                            <th style="text-align: right">{{ ($amount) ? number_format($amount,2) : "(".number_format($amount,2).")" }}</th>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="4" style="text-align: right">
                                                                <a href="{{ route('print_financial_report',$report) }}" target="_blank" class="btn btn-dark">Print</a>
                                                            </td>
                                                        </tr>
                                                    @else
                                                        <tr>
                                                            <td colspan="4">No Data Found</td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                            <!-- End Table with stripped rows -->
                                        </div>
                                    </div><!-- End Bordered Tabs Justified -->

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main><!-- End #main -->

    <x-modal />
@endsection


