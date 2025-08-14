<form method="POST" action="{{ route('accommodation') }}">
    @csrf

    <input type="hidden" value="{{ $accommodation_id }}" name="accommodation_id" />

    <table class="table">
        <thead>
        <tr>
            <th colspan="6">
                <x-button
                    type='button'
                    class="btn-primary btn-round ms-auto add_button float-end"
                    icon="bi bi-plus-lg"
                    name="Add Resident"
                    title="Add"
                />
{{--                <button class=" btn btn-outline-primary " > </button>--}}
            </th>
        </tr>
{{--        <tr>--}}
{{--            <th style="width: 4px" class="bg-primary text-white">#</th>--}}
{{--            <th class="bg-primary text-white">Residence Name</th>--}}
{{--            <th class="bg-primary text-white">Number of Blocks</th>--}}
{{--            <th class="bg-primary text-white">Gender</th>--}}
{{--            <th class="bg-primary text-white">Availability</th>--}}
{{--            <th style="width: 20px" class="bg-primary text-white">Action</th>--}}
{{--        </tr>--}}
        </thead>
        <tbody class="field_wrapper">
            @forelse ($accommodations as $key => $value)
                <tr class="align-middle accommodation_{{ $value->id }}">
                    <td>{{ ++$key }}</td>
                    <td>
                        <input type="hidden" name="accommodation[{{ $key }}][id]" value="{{ $value->id }}">
                        <x-input-text
                            type="text"
                            name="accommodation[{{ $key }}][name]"
                            required="true"
                            label="Residence Name"
                            value="{{ $value->name }}"
                        />
{{--                        <input type="text" value="{{ $value->name }}" class="form-control" name="accommodation[{{ $key }}][name]">--}}
                    </td>
                    <td style="width: 17%">
                        <x-input-text
                            type="number"
                            name="accommodation[{{ $key }}][total_blocks]"
                            required="true"
                            label="Blocks Number"
                            value="{{ $value->total_blocks }}"
                            min="1"
                            step="1"
                        />
{{--                        <input type="number" value="{{ $value->total_blocks }}" class="form-control" name="accommodation[{{ $key }}][total_blocks]">--}}
                    </td>
                    <td>
                        <x-input-select
                            :options="['Male', 'Female', 'Mixed']"
                            :selected="$value->gender"
                            name="accommodation[{{ $key }}][gender]"
                            :type="1"
                            :values="['M', 'F', 'A']"
                            required="true"
                            label="Gender"
                        />
{{--                        <input type="text" value="{{ $value->gender }}" class="form-control" name="accommodation[{{ $key }}][gender]">--}}
                    </td>
                    <td>
                        <x-input-select
                            :options="['Available', 'Block']"
                            :selected="$value->status"
                            name="accommodation[{{ $key }}][status]"
                            :type="1"
                            :values="['Active', 'Blocked']"
                            required="true"
                            label="Availability"
                        />
{{--                        <input type="text" value="{{ $value->status }}" class="form-control" name="accommodation[{{ $key }}][status]">--}}
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
                                'accommodation',
                                '/execute_form/delete/accommodation/{{ $value->id }}'
                            )"
                        />
                    </td>
                </tr>
            @empty
                <tr class="hid-table">
                    <td colspan="7">No Data Found</td>
                </tr>
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
            $('.hid-table').hide();
            //Check maximum number of input fields
            if(x < maxField){
                $(wrapper).append(`<tr class="align-middle">
                    <td>${x}</td>
                    <td>
                        <x-input-text
                            type="text"
                            name="accommodation[${x}][name]"
                            required="true"
                            label="Residence Name"
                            value=""
                        />
                    </td>
                    <td style="width: 17%">
                        <x-input-text
                            type="number"
                            name="accommodation[${x}][total_blocks]"
                            required="true"
                            label="Blocks Number"
                            value=""
                            min="1"
                            step="1"
                        />
                    </td>
                    <td>
                        <x-input-select
                            :options="['Male', 'Female', 'Mixed']"
                            :selected="0"
                            name="accommodation[${x}][gender]"
                            :type="1"
                            :values="['M', 'F', 'A']"
                            required="true"
                            label="Gender"
                        />
                    </td>
                    <td>
                        <x-input-select
                            :options="['Available', 'Block']"
                            :selected="0"
                            name="accommodation[${x}][status]"
                            :type="1"
                            :values="['Active', 'Blocked']"
                            required="true"
                            label="Availability"
                        />
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


