<form method="POST" action="{{ route('category') }}">
    @csrf
    @isset($category)
        @method('put')
        <input type="hidden" name="id" value="{{ $category->id }}">
    @endisset

    <div class="px-4 mb-3">
        <x-input-text
            type="text"
            name="look_up_name"
            required="true"
            label="Lookup Name"
            value="{{ isset($category) ? $category->look_up_name : '' }}"
        />
    </div>

    <div class="px-4 mb-3">
        <x-input-text
            type="text"
            name="lookup_short_code"
            required="true"
            label="Short Code"
            value="{{ isset($category) ? $category->lookup_short_code : '' }}"
        />
    </div>

    <div class="form-check form-switch mb-4" style="margin-left: 25px;">
        <input class="form-check-input" type="checkbox" role="switch" name="active_flag" id="active_flag" {{ (isset($category) && $category->active_flag == 1) ? 'checked' : (empty($category) ? 'checked' : '' ) }}>
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

