<form method="POST" action="{{ route('financial_entry') }}">
    @csrf
    @isset($financial_entry)
        @method('put')
        <input type="hidden" name="id" value="{{ $financial_entry->id }}">
    @endisset

    <div class="row">
        <div class="px-4 mb-3 col-6">
            <x-input-select
                :options="['Income', 'Expense']"
                :selected="isset($financial_entry) ? $financial_entry->entry_type : 0"
                name="entry_type"
                :type="1"
                :values="['Income', 'Expense']"
                required=""
                label="Entry Type"
            />
        </div>
        <div class="px-4 mb-3 col-6">
            <x-input-select
                :options="$transaction_types"
                :selected="isset($financial_entry) ? $financial_entry->transaction_type : 0"
                name="transaction_type"
                :type="0"
                required=""
                label="Transaction Type"
            />
        </div>
        <div class="px-4 mb-3 col-6">
            <x-input-text
                type="date"
                name="transaction_date"
                required="true"
                label="Transaction Date"
{{--                min="{{ date("Y-m-d") }}"--}}
                value="{{ isset($financial_entry) ? $financial_entry->transaction_date : '' }}"
            />
        </div>
        <div class="px-4 mb-3 col-6">
            <x-input-text
                type="number"
                name="amount"
                required="true"
                label="Amount"
                min="1"
                step="0.01"
                value="{{ isset($financial_entry) ? $financial_entry->amount : '' }}"
            />
        </div>
        <div class="px-4 mb-3 col-12">
            <x-input-text
                type="text"
                name="description"
                required="true"
                label="Description"
                value="{{ isset($financial_entry) ? $financial_entry->description : '' }}"
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

