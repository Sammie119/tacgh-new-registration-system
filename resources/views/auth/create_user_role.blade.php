<form method="POST" action="{{ route('user_roles') }}">
    @csrf

    <input type="hidden" value="{{ $user }}" name="id" />

    <table class="table">
        <thead>
            <tr>
                <th colspan="4">
                    <x-button
                        type='button'
                        class="btn-primary ms-auto add_button float-end"
                        icon="bi bi-plus-lg"
                        name="Add New"
                        title="Add New"
                    />
                </th>
            </tr>
            <tr>
                <th style="width: 4px" class="bg-primary text-white">#</th>
                <th class="bg-primary text-white">Role</th>
                <th class="bg-primary text-white">Permission</th>
                <th style="width: 4px" class="bg-primary text-white">Action</th>
            </tr>
        </thead>
        <tbody class="field_wrapper">
        @forelse ($assigned_roles as $key => $role)
            @php
                $role_id = $roles->where('name', $role)->first()->id;
                $role_permission = $role_to_permissions->where('role_id', $role_id)->first();
            @endphp
            <tr class="align-middle dropdown_{{ $key }}">
                <td>{{ ++$key }}</td>
                <td>
                    <x-input-select
                        :options="$roles"
                        :selected="$role_permission->role_id"
                        name="roles[]"
                        :type="'role'"
                        required="true"
                        label="Role"
                    />
                </td>
                <td>
                    <x-input-select
                        :options="$permissions"
                        :selected="$role_permission->permission_id"
                        name="permissions[]"
                        :type="'role'"
                        required="true"
                        label="Permission"
                    />
                </td>
                <td>
                    <x-button
                        type='button'
                        class="btn-icon btn-danger btn-sm remove_button"
                        icon="bi bi-trash-fill"
                        name=""
                        title="Delete"
                    />
                </td>
            </tr>
        @empty

        @endforelse
        </tbody>
    </table>

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

<script>
    $(document).ready(function(){
        let count = document.querySelectorAll('.align-middle').length;
        let x = count + 1; //Initial field counter is 1
        var maxField = 10; //Input fields increment limitation
        var addButton = $('.add_button'); //Add button selector
        var wrapper = $('.field_wrapper'); //Input field wrapper
        // var fieldHTML = `<div><input type="text" name="field_name[]" value=""/><button type="button" class="remove_button btn btn-sm btn-danger" title="Delete field">Del</button></div>`; //New input field html

        // Once add button is clicked
        $(addButton).click(function(){
            //Check maximum number of input fields
            if(x <= maxField){
                $(wrapper).append(`<tr class="align-middle">
                    <td>${x}</td>
                    <td>
                        <x-input-select
                            :options="$roles"
                            :selected="0"
                            name="roles[]"
                            :type="'role'"
                            required="true"
                            label="Role"
                        />
                    </td>
                    <td>
                        <x-input-select
                            :options="$permissions"
                            :selected="0"
                            name="permissions[]"
                            :type="'role'"
                            required="true"
                            label="Permission"
                        />
                    </td>
                    <td>
                        <x-button
                            type='button'
                            class="btn-icon btn-danger btn-sm remove_button"
                            icon="bi bi-trash-fill"
                            name=""
                            title="Delete field"
                        />
                    </td>
                </tr>`); //Add field html

                x++; //Increase field counter
            }else{
                alert('A maximum of '+maxField+' fields are allowed to be added. ');
            }
        });

        // var fieldHTML = ;

        // Once remove button is clicked
        $(wrapper).on('click', '.remove_button', function(e){
            e.preventDefault();
            $(this).closest('tr').remove(); //Remove field html
            x--; //Decrease field counter
        });
    });
</script>
