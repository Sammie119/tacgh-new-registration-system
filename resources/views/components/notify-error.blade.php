@props(['messages', 'type' => 0])

@empty($messages)

@else
    @if($type === 1)
        <div class="alert alert-success get-alert" role="alert">
            @foreach ((array) $messages as $message)
                <span class="text-success">{{ $message }}<br></span>
            @endforeach
        </div>
    @elseif($type === 2)
        <div class="alert alert-danger get-alert" role="alert">
            @foreach ((array) $messages as $message)
                <span class="text-danger">{{ $message }}<br></span>
            @endforeach
        </div>
    @else
        <div class="alert alert-danger get-alert" role="alert">
            @foreach ((array) $messages as $message)
                <span class="text-danger">{{ $message }}<br></span>
            @endforeach
        </div>
    @endif
@endempty

@push('scripts')
    <script>
        $(".get-alert").fadeTo(6000, 500).slideUp(500, function(){
            $(".get-alert").slideUp(500);
        });
    </script>
@endpush
