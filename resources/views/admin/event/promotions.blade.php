<form method="POST" action="{{ route('promotions') }}">
    @csrf

    <input type="hidden" value="{{ $event_id }}" name="event_id" />

    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Applies To</th>
            <th>Discount %</th>
            <th>Starts</th>
            <th>Ends</th>
            <th>Active</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($promotions as $key => $value)
            <tr class="align-middle promotions_{{ $value->id }}">
                <td>{{ $key + 1 }}</td>
                <td>
                    <input type="hidden" name="promotions[{{ $key }}][id]" value="{{ $value->id }}">
                    <x-input-text
                        type="text"
                        name="promotions[{{ $key }}][name]"
                        required="true"
                        label="Name"
                        value="{{ $value->name }}"
                    />
                </td>
                <td>
                    <x-input-select
                        :options="['Accommodation Only', 'Registration Only', 'Both']"
                        :selected="$value->applies_to"
                        name="promotions[{{ $key }}][applies_to]"
                        :values="['accommodation', 'registration_fee', 'both']"
                        :type="1"
                        required="true"
                        label="Applies To"
                    />
                </td>
                <td style="width: 12%">
                    <x-input-text
                        type="number"
                        name="promotions[{{ $key }}][discount_percentage]"
                        required="true"
                        label="Discount %"
                        value="{{ $value->discount_percentage }}"
                        min="0"
                        max="100"
                        step="0.01"
                    />
                </td>
                <td>
                    <x-input-text
                        type="datetime-local"
                        name="promotions[{{ $key }}][starts_at]"
                        required="true"
                        label="Starts"
                        value="{{ \Illuminate\Support\Carbon::parse($value->starts_at)->format('Y-m-d\TH:i') }}"
                    />
                </td>
                <td>
                    <x-input-text
                        type="datetime-local"
                        name="promotions[{{ $key }}][ends_at]"
                        required="true"
                        label="Ends"
                        value="{{ \Illuminate\Support\Carbon::parse($value->ends_at)->format('Y-m-d\TH:i') }}"
                    />
                </td>
                <td>
                    <div class="form-check form-switch" style="margin-left: 0.5rem">
                        <input class="form-check-input" type="checkbox" role="switch" name="promotions[{{ $key }}][active_flag]"
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
                            'promotions',
                            '/execute_form/delete/promotions/{{ $value->id }}'
                        )"
                    />
                </td>
            </tr>
        @endforeach
        <tr class="align-middle">
            <td>{{ $promotions->count() + 1 }}</td>
            <td>
                <x-input-text
                    type="text"
                    name="promotions[{{ $promotions->count() }}][name]"
                    label="Name"
                    value=""
                />
            </td>
            <td>
                <x-input-select
                    :options="['Accommodation Only', 'Registration Only', 'Both']"
                    :selected="'both'"
                    name="promotions[{{ $promotions->count() }}][applies_to]"
                    :values="['accommodation', 'registration_fee', 'both']"
                    :type="1"
                    label="Applies To"
                />
            </td>
            <td style="width: 12%">
                <x-input-text
                    type="number"
                    name="promotions[{{ $promotions->count() }}][discount_percentage]"
                    label="Discount %"
                    value=""
                    min="0"
                    max="100"
                    step="0.01"
                />
            </td>
            <td>
                <x-input-text
                    type="datetime-local"
                    name="promotions[{{ $promotions->count() }}][starts_at]"
                    label="Starts"
                    value=""
                />
            </td>
            <td>
                <x-input-text
                    type="datetime-local"
                    name="promotions[{{ $promotions->count() }}][ends_at]"
                    label="Ends"
                    value=""
                />
            </td>
            <td>
                <div class="form-check form-switch" style="margin-left: 0.5rem">
                    <input class="form-check-input" type="checkbox" role="switch" name="promotions[{{ $promotions->count() }}][active_flag]" checked>
                </div>
            </td>
            <td></td>
        </tr>
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
