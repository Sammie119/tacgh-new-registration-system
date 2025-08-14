<form method="POST" action="{{ route('accommodation_single') }}">
    @csrf
    @method('put')
    <input type="hidden" name="id" value="{{ $resident->id }}">

    <div class="px-4 mb-3">
        <x-input-text
            type="text"
            name="name"
            required="true"
            label="Resident Name"
            value="{{ $resident->name }}"
        />
    </div>
    <div class="px-4 mb-3">
        <x-input-text
            type="text"
            name="total_blocks"
            required="true"
            label="Total Blocks"
            value="{{ $resident->total_blocks }}"
        />
    </div>
    <div class="px-4 mb-3">
        <x-input-select
            :options="['Male', 'Female', 'Mixed']"
            :selected="$resident->gender"
            name="gender"
            :type="1"
            :values="['M', 'F', 'A']"
            required="true"
            label="Gender"
        />
    </div>

    <div class="d-flex justify-between">
        <div class="form-check form-switch mb-4" style="margin-left: 25px;">
            <input class="form-check-input" type="checkbox" role="switch" name="status" id="status" {{ (isset($resident) && $resident->status == "Active") ? 'checked' : (empty($resident) ? 'checked' : '' ) }}>
            <label class="form-check-label" for="active_flag">Availability</label>
        </div>

        <div class="form-check form-switch mb-4" style="margin-left: 25px;">
            <input class="form-check-input" type="checkbox" role="switch" name="active_flag" id="active_flag" {{ (isset($resident) && $resident->active_flag == 1) ? 'checked' : (empty($resident) ? 'checked' : '' ) }}>
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

