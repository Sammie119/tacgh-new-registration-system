<?php $income = $finance_income->sum('amount') + $online_payments->sum('amount_paid'); ?>
<?php $amount = $income - $finance_expense->sum('amount'); ?>
@extends("layouts.guest")

@section('content')
    <div class="container mt-5">
        <div style="text-align: center">
            <img src="{{ asset("assets/img/logo2.png") }}" alt="logo">
        </div>
        <table class="table">
{{--            <caption>{{ $header }}</caption>--}}
            <thead>
                <tr>
                    <th colspan="4" style="text-align: center"><h3>{{ $header }}</h3></th>
                </tr>
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
                        <td style="padding-left: 20px;">Online Payment - {{ $registrant_names[$online->reg_id] ?? null }}</td>
                        <td style="text-align: right">{{ number_format($online->amount_paid, 2) }}</td>
                        <td></td>
                    </tr>
                @endforeach
                @foreach($finance_income as $f_income)
                    <tr>
                        <td style="padding-left: 20px;">{{ ($f_income->transaction_type == 'Others') ? $f_income->description : ($dropdown_names[$f_income->transaction_type] ?? null) }}</td>
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
                        <td style="padding-left: 20px;">{{ ($expense->transaction_type == 'Others') ? $expense->description : ($dropdown_names[$expense->transaction_type] ?? null) }}</td>
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
            @else
                <tr>
                    <td colspan="4">No Data Found</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>

    <script>
        window.addEventListener("afterprint", function(event) {
            setTimeout(function() {
                window.close();
            }, 0);
        });

        // Initiate the print dialog
        window.print();
    </script>
@endsection
