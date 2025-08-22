<form method="POST" action="{{ route('download') }}" enctype="multipart/form-data">
    @csrf
    @isset($download)
        @method('put')
        <input type="hidden" name="id" value="{{ $download->id }}">
    @endisset

    <div class="px-4 mb-3">
        <x-input-text
            type="text"
            name="file_name"
            required="true"
            label="File Name"
            value="{{ isset($download) ? $download->file_name : '' }}"
        />
    </div>
    <div class="px-4 mb-3">
        <x-input-text
            type="file"
            name="file"
            required=""
            label=""
        />
    </div>

    <div class="d-flex justify-between">
        <div class="form-check form-switch mb-4" style="margin-left: 25px;">
            <input class="form-check-input" type="checkbox" role="switch" name="active_flag" value="1" id="active_flag" {{ (isset($download) && $download->active_flag == 1) ? 'checked' : (empty($download) ? 'checked' : '' ) }}>
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

