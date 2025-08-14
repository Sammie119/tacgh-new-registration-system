<form method="POST" action="{{ route('dropdown') }}">
    @csrf

    <input type="hidden" value="{{ $category_id }}" name="id" />

    <table class="table">
        <thead>
        <tr>
            <th colspan="4">
                <x-button
                    type='button'
                    class="btn-primary btn-round ms-auto add_button float-end"
                    icon="bi bi-plus-lg"
                    name="Add Dropdown"
                    title="Add"
                />
{{--                <button class=" btn btn-outline-primary " > </button>--}}
            </th>
        </tr>
        <tr>
            <th style="width: 4px" class="bg-primary text-white">#</th>
            <th class="bg-primary text-white">Dropdown Name</th>
            <th class="bg-primary text-white">Enabled</th>
            <th style="width: 20px" class="bg-primary text-white">Action</th>
        </tr>
        </thead>
        <tbody class="field_wrapper">
        @forelse ($dropdowns as $key => $value)
            <tr class="align-middle dropdown_{{ $value->id }}">
                <td>{{ ++$key }}</td>
                <td>
                    <input type="hidden" name="dropdown[{{ $key }}][id]" value="{{ $value->id }}">
                    <input type="text" value="{{ $value->full_name }}" class="form-control" name="dropdown[{{ $key }}][full_name]">
                </td>
                <td>
                    <div class="form-check form-switch" style="margin-left: 0.5rem">
                        <input class="form-check-input" type="checkbox" role="switch" name="dropdown[{{ $key }}][active_flag]"
                            {{ ($value->active_flag == 1) ? 'checked' : '' }}>
                    </div>
                </td>
                <td>
                    <x-button
                        type='button'
                        class="btn-icon btn-danger btn-sm"
                        icon="bi bi-trash-fill"
                        name=""
                        title="Delete"
                        onclick="deleteFunction(
                            {{ $value->id }},
                            'dropdown',
                            '/execute_form/delete/dropdown/{{ $value->id }}'
                        )"
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
        var maxField = 20; //Input fields increment limitation
        var addButton = $('.add_button'); //Add button selector
        var wrapper = $('.field_wrapper'); //Input field wrapper
        // var fieldHTML = `<div><input type="text" name="field_name[]" value=""/><button type="button" class="remove_button btn btn-sm btn-danger" title="Delete field">Del</button></div>`; //New input field html

        // Once add button is clicked
        $(addButton).click(function(){
            //Check maximum number of input fields
            if(x < maxField){
                $(wrapper).append(`<tr class="align-middle">
                    <td>${x}</td>
                    <td>
                        <input type="text" class="form-control" name="dropdown[${x}][full_name]">
                    </td>
                    <td>
                        <div class="form-check form-switch" style="margin-left: 0.5rem">
                            <input class="form-check-input" type="checkbox" role="switch" name="dropdown[${x}][active_flag]">
                        </div>
                    </td>
                    <td>
                        <x-button
                            type='button'
                            class="btn-icon btn-danger remove_button btn-sm"
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

        // Once remove button is clicked
        $(wrapper).on('click', '.remove_button', function(e){
            e.preventDefault();
            $(this).closest('tr').remove(); //Remove field html
            x--; //Decrease field counter
        });
    });
</script>


