<form method="POST" action="{{ route('role') }}">
    @csrf
    @isset($role)
        @method('put')
        <input type="hidden" name="id" value="{{ $role->id }}">
    @endisset

    <div class="px-4 mb-3">
        <x-input-text
            type="text"
            name="name"
            required="true"
            label="Role Name"
            value="{{ isset($role) ? $role->name : '' }}"
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

