<form method="POST" action="{{ route('store_online_payment_correction') }}">
    @csrf

    <div class="row">
        <div class="px-4 mb-3 col-12">
            <div class="input-group input-group-sm">
                <input type="text" placeholder="Registration Number" list="attendee_id" name="registration_no" required class="form-control">
                <datalist id="attendee_id">
                    @foreach($registrants as $participant)
                        <option value="{{ $participant->registration_no }}">{{ $participant->name }}</option>
                    @endforeach
                </datalist>
            </div>
        </div>
        <div class="px-4 mb-3 col-6">
            <x-input-text
                type="text"
                name="transaction_no"
                required="true"
                label="Transaction No"
                value=""
            />
        </div>
        <div class="px-4 mb-3 col-6">
            <x-input-text
                type="date"
                name="date_paid"
                required="true"
                label="Date Paid"
                value=""
            />
        </div>
        <div class="px-4 mb-3 col-6">
            <x-input-text
                type="number"
                name="amount_paid"
                required="true"
                label="Amount"
                min="1"
                step="0.01"
                value=""
            />
        </div>
        <div class="px-4 mb-3 col-6">
            <x-input-text
                type="text"
                name="payment_mode"
                required="true"
                label="Payment Mode"
                value=""
            />
        </div>
        <div class="px-4 mb-3 col-6">
            <x-input-text
                type="text"
                name="batch_no"
                required="true"
                label="Batch No"
                value=""
            />
        </div>
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

