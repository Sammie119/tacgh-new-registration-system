@props(['options'=> [], 'placeholder' => '', 'list', 'value' => '', 'label' => '', 'person_type' => 'student'])

<div class="form-floating form-floating-custom">
    <input
        type="text"
        {!! $attributes->merge(['class'=>'form-control']) !!}
        value="{{ $value }}"
        list="{{ $list }}"
        placeholder="{{$placeholder}}"
        style="padding-top: 30px"
    >
    <datalist id="{{ $list }}">
        @forelse ($options as $option)
            <option value="{{ $option->admission_no }}">{{ get_person_name($option->id, $person_type) }}"</option>
        @empty
            <option value="No Data Found">
        @endforelse
    </datalist>
    <label for="datalist">{{ $label }}</label>
</div>
