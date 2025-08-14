<form method="POST" action="{{ route('blocks') }}">
    @csrf

    <input type="hidden" value="{{ $resident->id }}" name="resident_id" />

    <table class="table">
        <tbody>
        @if($blocks->count() > 0)
            @foreach ($blocks as $key => $value)
                <tr class="align-middle">
                    <td>{{ ++$key }}</td>
                    <td>
                        <input type="hidden" name="blocks[{{ $key }}][id]" value="{{ $value->id }}">
                        <x-input-text
                            type="text"
                            name="blocks[{{ $key }}][name]"
                            required="true"
                            label="Block Name"
                            value="{{ $value->name }}"
                        />
                    </td>
                    <td style="width: 14%">
                        <x-input-text
                            type="number"
                            name="blocks[{{ $key }}][total_floors]"
                            required="true"
                            label="Total Floors"
                            value="{{ $value->total_floors }}"
                            min="1"
                            step="1"
                        />
                    </td>
                    <td style="width: 14%">
                        <x-input-text
                            type="number"
                            name="blocks[{{ $key }}][total_rooms]"
                            readonly
                            label="Total Rooms"
                            value="{{ $value->total_rooms }}"
                            min="1"
                            step="1"
                        />
                    </td>
                    <td>
                        <x-input-select
                            :options="['Male', 'Female', 'Mixed']"
                            :selected="$value->gender"
                            name="blocks[{{ $key }}][gender]"
                            :type="1"
                            :values="['M', 'F', 'A']"
                            required="true"
                            label="Gender"
                        />
                    </td>
                    <td>
                        <x-input-select
                            :options="['Available', 'Block']"
                            :selected="$value->status"
                            name="blocks[{{ $key }}][status]"
                            :type="1"
                            :values="['Active', 'Blocked']"
                            required="true"
                            label="Availability"
                        />
                    </td>
                    <td style="width: 10px">
                        <x-button
                            type='button'
                            class="btn-danger"
                            icon="ri-hotel-bed-fill fs-5"
                            name=""
                            data-bs-toggle="modal"
                            data-bs-target="#exampleModalToggle2"
                            data-bs-title2="Generate Rooms for {{ $value->name }} Block"
                            data-bs-url2="/execute_form/view/generate_rooms/{{ $value->id }}"
                            data-bs-size2="modal-xl"
                        />
                    </td>
                </tr>
            @endforeach
        @else
            @for ($i = 1; $i <= $resident->total_blocks; $i++)
                <tr class="align-middle block_{{ $i }}">
                    <td>{{ $i }}</td>
                    <td>
                        <x-input-text
                            type="text"
                            name="blocks[{{ $i }}][name]"
                            required="true"
                            label="Block Name"
                            value=""
                        />
                    </td>
                    <td style="width: 17%">
                        <x-input-text
                            type="number"
                            name="blocks[{{ $i }}][total_floors]"
                            required="true"
                            label="Total Floors"
                            value=""
                            min="1"
                            step="1"
                        />
                    </td>
                    <td style="width: 18%">
                        <x-input-text
                            type="number"
                            name="blocks[{{ $i }}][total_rooms]"
                            readonly
                            label="Total Rooms"
                            value="0"
                            min="1"
                            step="1"
                        />
                    </td>
                    <td>
                        <x-input-select
                            :options="['Male', 'Female', 'Mixed']"
                            :selected="0"
                            name="blocks[{{ $i }}][gender]"
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
                            name="blocks[{{ $i }}][status]"
                            :type="1"
                            :values="['Active', 'Blocked']"
                            required="true"
                            label="Availability"
                        />
                    </td>
                </tr>
            @endfor
        @endif
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


