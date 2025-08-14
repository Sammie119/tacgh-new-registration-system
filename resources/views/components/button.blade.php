@props(['type' => 'button', 'icon' => 'bi bi-plus-lg', 'name' => 'Button'])
<button type = '{{$type}}' {{ $attributes->merge(['class' => 'btn rounded-pill']) }}>
    <i class="{{$icon}}"></i>
    {{ $name }}
</button>
