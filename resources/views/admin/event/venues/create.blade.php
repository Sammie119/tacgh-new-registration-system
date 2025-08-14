<form method="POST" action="{{ route('event') }}">
    @csrf
    @isset($event)
        @method('put')
        <input type="hidden" name="id" value="{{ $event->id }}">
    @endisset

    <div class="px-4 mb-3">
        <x-input-text
            type="text"
            name="name"
            required="true"
            label="Event Name"
            value="{{ isset($event) ? $event->name : '' }}"
        />
    </div>
    <div class="px-4 mb-3">
        <x-input-text
            type="text"
            name="description"
            required="true"
            label="Description"
            value="{{ isset($event) ? $event->description : '' }}"
        />
    </div>
    <div class="px-4 mb-3">
        <x-input-text
            type="text"
            name="code_prefix"
            required="true"
            label="Code Prefix"
            value="{{ isset($event) ? $event->code_prefix : '' }}"
        />
    </div>
    <div class="px-4 mb-3">
        <x-input-text
            type="date"
            name="start_date"
            required="true"
            label="Start Date"
{{--            min="{{ date("Y-m-d") }}"--}}
            value="{{ isset($event) ? $event->start_date : '' }}"
        />
    </div>
    <div class="px-4 mb-3">
        <x-input-text
            type="date"
            name="end_date"
            required="true"
            label="End Date"
{{--            min="{{ date("Y-m-d") }}"--}}
            value="{{ isset($event) ? $event->end_date : '' }}"
        />
    </div>

    <div class="d-flex justify-between">
        <div class="form-check form-switch mb-4" style="margin-left: 25px;">
            <input class="form-check-input" type="checkbox" role="switch" name="is_payment_required" id="is_payment_required" {{ (isset($event) && $event->is_payment_required == "Yes") ? 'checked' : (empty($event) ? 'checked' : '' ) }}>
            <label class="form-check-label" for="active_flag">Is Payment Required</label>
        </div>

        <div class="form-check form-switch mb-4" style="margin-left: 25px;">
            <input class="form-check-input" type="checkbox" role="switch" name="active_flag" id="active_flag" {{ (isset($event) && $event->active_flag == 1) ? 'checked' : (empty($event) ? 'checked' : '' ) }}>
            <label class="form-check-label" for="active_flag">Enable</label>
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

