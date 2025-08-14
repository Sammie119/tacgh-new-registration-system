@extends('layouts.guest')

@section('content')
    <main class="mt-4">
        <div class="container" style="max-width: 70%;">
            <h1>{{ $form->title }}</h1>
            <section class="section">

                <p>{{ $form->description }}</p>

    {{--            @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif--}}
                <x-notify-error :messages="$errors->all()" />
                <x-notify-error :messages="Session::get('success')" :type="1"/>
                <x-notify-error :messages="Session::get('error')" :type="2"/>

                <form method="POST" action="{{ route('forms.submit', $form->slug) }}">
                    @csrf

                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3 mt-3">
                                <label>Your name (optional)</label>
                                <input type="text" name="submitter_name" class="form-control" value="{{ old('submitter_name') }}">
                            </div>
                            <div class="mb-3">
                                <label>Your email (optional)</label>
                                <input type="email" name="submitter_email" class="form-control" value="{{ old('submitter_email') }}">
                            </div>
                        </div>
                    </div>


                    @foreach($form->fields as $field)
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title form-label">{{ $field->label }} @if($field->is_required) * @endif</h5>
{{--                                <div class="row g-3">--}}
                                    <div class="mb-3">
{{--                                        <label class="form-label"></label>--}}
                                        @php $name = 'field_'.$field->id; @endphp

                                        @if(in_array($field->field_type, ['short_text','email','number','date']))
                                            <input type="{{ $field->field_type === 'short_text' ? 'text' : $field->field_type }}"
                                                   name="{{ $name }}"
                                                   value="{{ old($name) }}"
                                                   class="form-control" @if($field->is_required) required @endif>
                                        @elseif($field->field_type === 'paragraph')
                                            <textarea name="{{ $name }}" class="form-control" @if($field->is_required) required @endif>{{ old($name) }}</textarea>
                                        @elseif(in_array($field->field_type, ['radio','dropdown','checkbox']))
                                            @php $opts = $field->options ?? []; @endphp

                                            @if($field->field_type === 'dropdown')
                                                <select name="{{ $name }}" class="form-control" @if($field->is_required) required @endif>
                                                    <option value="">Select</option>
                                                    @foreach($opts as $opt)
                                                        <option value="{{ $opt }}" {{ old($name) == $opt ? 'selected':'' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($field->field_type === 'radio')
                                                @foreach($opts as $opt)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="{{ $name }}" value="{{ $opt }}" id="f{{ $field->id }}{{ \Illuminate\Support\Str::slug($opt) }}">
                                                        <label class="form-check-label" for="f{{ $field->id }}{{ \Illuminate\Support\Str::slug($opt) }}">{{ $opt }}</label>
                                                    </div>
                                                @endforeach
                                            @else {{-- checkbox --}}
                                            @foreach($opts as $opt)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="{{ $name }}[]" value="{{ $opt }}" id="f{{ $field->id }}{{ \Illuminate\Support\Str::slug($opt) }}">
                                                    <label class="form-check-label" for="f{{ $field->id }}{{ \Illuminate\Support\Str::slug($opt) }}">{{ $opt }}</label>
                                                </div>
                                            @endforeach
                                            @endif
                                        @endif

                                        @error($name) <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>
{{--                                </div>--}}
                            </div>
                        </div>
                    @endforeach

{{--                    <button type="submit" class="btn btn-primary mb-4">Submit</button>--}}

                    <div class="modal-footer mb-4">
                        <x-button
                            type='button'
                            class="btn-danger rounded-pill"
                            icon="bi bi-x-lg"
                            name="Close"
                            onclick="window.close();"
                        />
                        <x-button
                            type='submit'
                            class="btn-success rounded-pill"
                            icon="bi bi-save2"
                            name="Submit"
                        />
                    </div>
                </form>
            </section>
        </div>
    </main>
@endsection
