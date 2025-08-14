<form method="POST" action="{{ route('register') }}">
    @csrf
    @isset($user)
        @method('put')
        <input type="hidden" name="id" value="{{ $user->id }}">
    @endisset

    <div class="px-4 mb-3">
        <x-input-text
            type="text"
            name="name"
            label="Name"
            value="{{ isset($user) ? $user->name : '' }}"
            required="true"
        />
    </div>

    <div class="px-4 mb-3">
        <x-input-text
            type="email"
            name="email"
            label="Email"
            value="{{ isset($user) ? $user->email : '' }}"
            required="true"
        />
    </div>

    <div class="px-4 mb-3">
        <x-input-text
            type="password"
            name="password"
            required="{{ isset($user) ? '' : 'true' }}"
            label="Password"
        />
    </div>

    <div class="px-4 mb-3">
        <x-input-text
            type="password"
            name="password_confirmation"
            required="{{ isset($user) ? '' : 'true' }}"
            label="Confirm Password"
        />
    </div>

    <div class="form-check form-switch mb-4" style="margin-left: 25px;">
        <input class="form-check-input" type="checkbox" role="switch" name="active_flag" id="active_flag" {{ (isset($user) && $user->active_flag == 1) ? 'checked' : (empty($user) ? 'checked' : '' ) }}>
        <label class="form-check-label" for="active_flag">Enable</label>
    </div>

    {{-- Buttons --}}
    <div class="modal-footer">
        <x-button
            type='button'
            class="btn-danger rounded-pill"
            icon="bi bi-x-lg"
            name="Close"
            data-bs-dismiss="modal"
        />
        <x-button
            type='submit'
            class="btn-success rounded-pill"
            icon="bi bi-save2"
            name="Submit"
        />
    </div>
</form>

