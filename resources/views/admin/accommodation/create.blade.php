<form method="POST" action="{{ route('venue') }}">
    @csrf
    @isset($venue)
        @method('put')
        <input type="hidden" name="id" value="{{ $venue->id }}">
    @endisset


    <div class="px-4 mb-3">
        <x-input-text
            type="text"
            name="name"
            required="true"
            label="Venue Name"
            value="{{ isset($venue) ? $venue->name : '' }}"
        />
    </div>

    <div class="px-4 mb-3">
        <x-input-text
            type="text"
            name="location"
            required="true"
            label="Location"
            value="{{ isset($venue) ? $venue->location : '' }}"
        />
    </div>

    <div class="px-4 mb-3">
        <x-input-select
            :options="$regions"
            :selected="isset($venue) ? $venue->region_id : 0"
            name="region_id"
            :type="0"
            required="true"
            label="Region"
        />
    </div>

    <div class="form-check form-switch mb-4" style="margin-left: 25px;">
        <input class="form-check-input" type="checkbox" role="switch" name="active_flag" id="active_flag" {{ (isset($venue) && $venue->active_flag == 1) ? 'checked' : (empty($venue) ? 'checked' : '' ) }}>
        <label class="form-check-label" for="active_flag">Enable</label>
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

