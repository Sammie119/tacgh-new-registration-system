@props(['options'=> [], 'selected' => 0, 'type' => 0, 'values' => [], 'required' => false, 'name', 'label', 'get_name' =>''])
<div class="form-floating form-floating-custom">

    <select
        {!! $attributes->merge(['class'=>'form-control']) !!}
        {{ $required ? 'required' : '' }}
        id="{{ $name }}"
        name="{{ $name }}"
    >
        <option selected disabled value="">--Select--</option>
        @if($type == 0)
            @foreach ($options as $option)
                <option @if ($selected == $option['id']) selected @endif value="{{ $option['id'] }}">{{ $option['name'] }}</option>
            @endforeach

        @elseif($type == 'role')
            @foreach ($options as $option)
                <option @if ($selected == $option['id']) selected @endif value="{{ $option['name'] }}">{{ $option['name'] }}</option>
            @endforeach

        @else
            @foreach ($options as $key => $option)
                <option @if ($selected == $values[$key]) selected @endif value="{{ $values[$key] }}">{{ $option }}</option>
            @endforeach
        @endif
    </select>
    <label for="{{ $name }}">{{ $label }} <span style="color: red">{{ $required ? '*' : '' }}</span></label>
</div>

