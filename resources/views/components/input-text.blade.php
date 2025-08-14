@props(['type' => 'text', 'name', 'required' => false, 'label', 'row' => 5])

@if($type === 'textarea')
    <div class="form-floating form-floating-custom">
        <textarea
            {!! $attributes->merge(['class' => 'form-control']) !!}
            name="{{ $name }}"
            id="{{ $name }}"
            rows="{{ $row }}"
            {{ $required ? 'required' : '' }}
        >{{ $slot }}</textarea>
        <label for="{{ $name }}">{{ $label }} <span style="color: red">{{ $required ? '*' : '' }}</span></label>
    </div>
@else
    <div class="form-floating form-floating-custom">
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            {!! $attributes->merge(['class' => 'form-control']) !!}
            id="{{ $name }}"
            placeholder=""
            {{ $required ? 'required' : '' }}
        />
        <label for="{{ $name }}">{{ $label }} <span style="color: red">{{ $required ? '*' : '' }}</span></label>
    </div>
@endif


