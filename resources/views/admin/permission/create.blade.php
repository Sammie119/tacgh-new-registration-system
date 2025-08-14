<form method="POST" action="{{ route('permission') }}">
    @csrf
    @isset($permission)
        @method('put')
        <input type="hidden" name="id" value="{{ $permission->id }}">
    @endisset

    <div class="px-4 mb-3">
        <x-input-text
            type="text"
            name="name"
            required="true"
            label="Permission Name"
            value="{{ isset($permission) ? $permission->name : '' }}"
        />
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

