<form action="{{ route('financial_clearance') }}" method="post">
    <input type="hidden" name="payment_id" value="{{ $payment->id }}">
    @csrf
    <div>
        <h5>Clearance for {{ event_registrant_name($payment->reg_id) }}</h5>
        <p>Paid <strong>{{ $payment_made }}</strong> out of <strong>{{ $payment->amount_to_pay }}</strong></p>
    </div>
    <div>
        <input type="text" name="comment" class="form-control" placeholder="Enter Comment">
    </div>

    {{-- Buttons --}}
    <div class="modal-footer">
        <x-button
            type='button'
            class="btn-danger btn-round"
            icon="bi bi-x-lg"
            name="Close"
            data-bs-dismiss="modal"
        />
        <x-button
            type='submit'
            class="btn-success btn-round"
            icon="bi bi-save2"
            name="Submit"
        />
    </div>
</form>
