<form method="POST" action="{{ route('store_online_payment_correction') }}">
    @csrf
    <div class="row">
        <div class="px-4 mb-3 col-6">
            <div class="input-group input-group-sm">
                <input type="text" placeholder="Enter Reference Number" id="payment_reference" required class="form-control" style="height: 38px">
                <span class="input-group-btn">
                  <button type="button" id="paymentCheck" class="btn btn-warning btn-flat">Check</button>
                </span>
            </div>
        </div>

        <div class="px-4 mb-3 col-6">
            <div class="input-group input-group-sm">
                <input type="text" placeholder="Registration Number" list="attendee_id" name="registration_no" required class="form-control" style="height: 38px">
                <datalist id="attendee_id">
                    @foreach($registrants as $participant)
                        <option value="{{ $participant->registration_no }}">{{ $participant->name }}</option>
                    @endforeach
                </datalist>
            </div>
        </div>
        <div class="px-4 mb-3 col-12">
            <x-input-text
                type="text"
                name="payee_name"
                required="true"
                label="Payee Name"
                id="payee_name"
                value=""
                readonly
            />
        </div>
        <div class="px-4 mb-3 col-12">
            <x-input-text
                type="text"
                name="payee_email"
                id="payee_email"
                required="true"
                label="Payee Email"
                value=""
                readonly
            />
        </div>
        <div class="px-4 mb-3 col-6">
            <x-input-text
                type="text"
                name="transaction_no"
                required="true"
                label="Transaction No"
                value=""
                readonly
            />
        </div>
        <div class="px-4 mb-3 col-6">
            <x-input-text
                type="date"
                name="date_paid"
                required="true"
                label="Date Paid"
                value=""
                readonly
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
                readonly
            />
        </div>

        <div class="px-4 mb-3 col-6">
            <x-input-text
                type="text"
                name="payment_mode"
                required="true"
                label="Payment Mode"
                value=""
                readonly
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

<script>
    $(document).ready(function () {
        $('#paymentCheck').on('click', function () {

            var reference = $('#payment_reference').val();

            $.ajax({
                url: "{{ route('check_payment_confirmation') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    reference: reference
                },
                success: function (response) {
                    if (response.data.status === 'success') {
                        const isoString = response.data.paid_at;
                        const date = new Date(isoString);

                        const formattedDate = date.toISOString().split('T')[0];

                        const amount = response.data.amount / 100;
                        const formattedAmount = amount.toFixed(2);

                        $('#payee_name').val(response.data.metadata.name);
                        $('#payee_email').val(response.data.customer.email);
                        $('#transaction_no').val(response.data.id);
                        $('#date_paid').val(formattedDate);
                        $('#amount_paid').val(formattedAmount);
                        $('#payment_mode').val(response.data.channel);
                    } else {
                        alert('Payment not found!');
                    }
                }
            });
        });
    });
</script>
