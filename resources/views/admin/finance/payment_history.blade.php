<div>
    <h5>Payment History for {{ $registrant_name }}</h5>

    <table class="table table-sm">
        <thead>
        <tr>
            <th>#</th>
            <th>Amount Paid</th>
            <th>Mode</th>
            <th>Transaction No.</th>
            <th>Date Paid</th>
            <th>Approved</th>
            <th>Comment</th>
        </tr>
        </thead>
        <tbody>
        @forelse($payments as $key => $payment)
            <tr>
                <td>{{ ++$key }}</td>
                <td>{{ number_format($payment->amount_paid, 2) }}</td>
                <td>{{ $payment->payment_mode }}</td>
                <td>{{ $payment->transaction_no }}</td>
                <td>{{ $payment->date_paid }}</td>
                <td>{{ (int) $payment->approved === 2 ? 'Approved' : 'Disapproved' }}</td>
                <td>{{ $payment->comment }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7">No Data Found</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="modal-footer">
        <x-button
            type='button'
            class="btn-danger btn-round"
            icon="bi bi-x-lg"
            name="Close"
            data-bs-dismiss="modal"
        />
    </div>
</div>
