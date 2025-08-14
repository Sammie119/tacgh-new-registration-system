<form method="POST" action="{{ route('assign_permissions') }}">
    @csrf
    @method('put')

    <input type="hidden" value="{{ $role }}" name="id" />

    <div class="row" style="margin-left: 5%">
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link active" aria-current="page"><b>Permissions</b></a>
            </li>
        </ul>
        @foreach($permissions as $permission)
            <div class="col-md-4 mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" {{ in_array($permission->id, $get_permissions) ? 'checked' : '' }} type="checkbox" name="permissions[]" value="{{ $permission->name }}" role="switch" id="flexSwitchCheckChecked_{{ $permission->id }}" >
                    <label class="form-check-label" for="flexSwitchCheckChecked_{{ $permission->id }}" style="margin-left: 5px">{{ $permission->name }}</label>
                </div>
            </div>
        @endforeach
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


