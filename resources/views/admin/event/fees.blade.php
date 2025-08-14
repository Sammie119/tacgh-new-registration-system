<!-- Default Tabs -->
<ul class="nav nav-tabs d-flex" id="myTabjustified" role="tablist">
    <li class="nav-item flex-fill" role="presentation">
        <button class="nav-link w-100 active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-justified" type="button" role="tab" aria-controls="home" aria-selected="true">
            Registration Fees
        </button>
    </li>
    <li class="nav-item flex-fill" role="presentation">
        <button class="nav-link w-100" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-justified" type="button" role="tab" aria-controls="profile" aria-selected="false">
            Accommodation Fees
        </button>
    </li>
</ul>
@php
    $acc = \App\Models\Admin\Dropdown::select('full_name')->where('lookup_code_id', 9)->get()->pluck('full_name')->toArray();
    $fees = \App\Models\Admin\Dropdown::select('full_name')->where('lookup_code_id', 8)->get()->pluck('full_name')->toArray();
@endphp
<div class="tab-content pt-2" id="myTabjustifiedContent">
    <div class="tab-pane fade show active" id="home-justified" role="tabpanel" aria-labelledby="home-tab">

        <form method="POST" action="{{ route('fees') }}">
            @csrf

            <input type="hidden" value="{{ $event_id }}" name="event_id" />
            <input type="hidden" value="registration_fee" name="fee_type" />

            <table class="table">
                <thead>
                <tr>
                    <th colspan="6">
                        <x-button
                            type='button'
                            class="btn-primary btn-round ms-auto add_button float-end"
                            icon="bi bi-plus-lg"
                            name="Add Registration Fees"
                            title="Add"
                        />
                        {{--                <button class=" btn btn-outline-primary " > </button>--}}
                    </th>
                </tr>

                </thead>
                <tbody class="field_wrapper">
                @forelse ($registration as $key => $value)
                    <tr class="align-middle tb-data registration_{{ $value->id }}">
                        <td>{{ ++$key }}</td>
                        <td>
                            <input type="hidden" name="fees[{{ $key }}][id]" value="{{ $value->id }}">
                            <x-input-select
                                :options="$fees"
                                :selected="$value->description"
                                name="fees[{{ $key }}][description]"
                                :values="$fees"
                                :type="1"
                                required="true"
                                label="Description"
                            />
{{--                            <x-input-text--}}
{{--                                type="text"--}}
{{--                                name="fees[{{ $key }}][description]"--}}
{{--                                required="true"--}}
{{--                                label="Description"--}}
{{--                                value="{{ $value->description }}"--}}
{{--                            />--}}
                        </td>
                        <td style="width: 17%">
                            <x-input-text
                                type="number"
                                name="fees[{{ $key }}][fee_amount]"
                                required="true"
                                label="Amount"
                                value="{{ $value->fee_amount }}"
                                min="0"
                                step="1"
                            />
                        </td>
                        <td>
                            <div class="form-check form-switch" style="margin-left: 0.5rem">
                                <input class="form-check-input" type="checkbox" role="switch" name="fees[{{ $key }}][active_flag]"
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
                                    'registration',
                                    '/execute_form/delete/fees/{{ $value->id }}'
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

    </div>
    <div class="tab-pane fade" id="profile-justified" role="tabpanel" aria-labelledby="profile-tab">

        <form method="POST" action="{{ route('fees') }}">
            @csrf

            <input type="hidden" value="{{ $event_id }}" name="event_id" />
            <input type="hidden" value="accommodation" name="fee_type">

            <table class="table">
                <thead>
                <tr>
                    <th colspan="6">
                        <x-button
                            type='button'
                            class="btn-primary btn-round ms-auto add_button2 float-end"
                            icon="bi bi-plus-lg"
                            name="Add Accommodation Fees"
                            title="Add"
                        />
                        {{--                <button class=" btn btn-outline-primary " > </button>--}}
                    </th>
                </tr>

                </thead>
                <tbody class="field_wrapper2">
                @forelse ($accommodation as $key => $value)
                    <tr class="align-middle tb-data2 accommodation_{{ $value->id }}">
                        <td>{{ ++$key }}</td>
                        <td>
                            <input type="hidden" name="fees[{{ $key }}][id]" value="{{ $value->id }}">
                            <x-input-select
                                :options="$acc"
                                :selected="$value->description"
                                name="fees[{{ $key }}][description]"
                                :values="$acc"
                                :type="1"
                                required="true"
                                label="Description"
                            />
{{--                            <x-input-text--}}
{{--                                type="text"--}}
{{--                                name="fees[{{ $key }}][description]"--}}
{{--                                required="true"--}}
{{--                                label="Description"--}}
{{--                                value="{{ $value->description }}"--}}
{{--                            />--}}
                        </td>
                        <td style="width: 17%">
                            <x-input-text
                                type="number"
                                name="fees[{{ $key }}][fee_amount]"
                                required="true"
                                label="Amount"
                                value="{{ $value->fee_amount }}"
                                min="0"
                                step="1"
                            />
                        </td>
                        <td>
                            <div class="form-check form-switch" style="margin-left: 0.5rem">
                                <input class="form-check-input" type="checkbox" role="switch" name="fees[{{ $key }}][active_flag]"
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
                                    'accommodation',
                                    '/execute_form/delete/fees/{{ $value->id }}'
                            )"
                            />
                        </td>
                    </tr>
                @empty
                    <tr class="hid-table2">
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

    </div>
</div><!-- End Default Tabs -->

<script>
    $(document).ready(function(){
        let count = document.querySelectorAll('.tb-data').length;
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
                        <x-input-select
                            :options="$fees"
                            :selected="''"
                            name="fees[${x}][description]"
                            :values="$fees"
                            :type="1"
                            required="true"
                            label="Description"
                        />
                    </td>
                    <td style="width: 17%">
                        <x-input-text
                            type="number"
                            name="fees[${x}][fee_amount]"
                            required="true"
                            label="Amount"
                            value=""
                            min="0"
                            step="1"
                        />
                    </td>
                    <td>
                        <div class="form-check form-switch" style="margin-left: 0.5rem">
                            <input class="form-check-input" type="checkbox" role="switch" name="fees[${x}][active_flag]" checked>
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

<script>
    $(document).ready(function(){
        let count2 = document.querySelectorAll('.tb-data2').length;
        let x2 = count2 + 1; //Initial field counter is 1
        var maxField2 = 20; //Input fields increment limitation
        var addButton2 = $('.add_button2'); //Add button selector
        var wrapper2 = $('.field_wrapper2'); //Input field wrapper
        // var fieldHTML = `<div><input type="text" name="field_name[]" value=""/><button type="button" class="remove_button btn btn-sm btn-danger" title="Delete field">Del</button></div>`; //New input field html

        // Once add button is clicked
        $(addButton2).click(function(){
            $('.hid-table2').hide();
            //Check maximum number of input fields
            if(x2 < maxField2){
                $(wrapper2).append(`<tr class="align-middle">
                    <td>${x2}</td>
                    <td>
                        <x-input-select
                            :options="$acc"
                            :selected="''"
                            name="fees[${x2}][description]"
                            :values="$acc"
                            :type="1"
                            required="true"
                            label="Description"
                        />
                    </td>
                    <td style="width: 17%">
                        <x-input-text
                            type="number"
                            name="fees[${x2}][fee_amount]"
                            required="true"
                            label="Amount"
                            value=""
                            min="0"
                            step="1"
                        />
                    </td>
                    <td>
                        <div class="form-check form-switch" style="margin-left: 0.5rem">
                            <input class="form-check-input" type="checkbox" role="switch" name="fees[${x2}][active_flag]" checked>
                        </div>
                    </td>
                    <td>
                        <x-button
                            type='button'
                            class="btn-icon btn-danger remove_button2 btn-sm"
                            icon="bi bi-trash-fill"
                            name=""
                            title="Delete field"
                        />
                    </td>
                </tr>`); //Add field html
                x2++; //Increase field counter
            }else{
                alert('A maximum of '+maxField2+' fields are allowed to be added. ');
            }
        });

        // Once remove button is clicked
        $(wrapper2).on('click', '.remove_button2', function(e){
            e.preventDefault();
            $(this).closest('tr').remove(); //Remove field html
            x2--; //Decrease field counter
        });
    });
</script>
