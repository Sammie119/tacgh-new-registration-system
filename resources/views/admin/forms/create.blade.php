
<div class="container">
    <form method="POST" action="{{ route('forms.store') }}">
        @csrf
        @isset($form)
            <input type="hidden" value="{{ $form->id }}" name="id">
            @method("PUT")
        @endisset

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input name="title" class="form-control" required value="{{ isset($form)? $form->title : old('title') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control">{{ isset($form)? $form->description : old('description') }}</textarea>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_public" name="is_public" {{ (isset($form) && $form->is_public == 1) ? 'checked' : (empty($form) ? 'checked' : '' ) }}>
            <label class="form-check-label" for="is_public">Public (anyone with link can respond)</label>
        </div>

        <h4>Fields</h4>
        <hr>
        <div id="fields">
            @isset($form)
                @foreach($form->fields as $key => $field)
                    <input type="hidden" value="{{ $field->id }}" name="fields[{{$key}}][field_id]">
                    <div class="card mb-2 field-block p-3">
                        <div class="mb-2">
                            <label>Label</label>
                            <input name="fields[{{$key}}][label]" value="{{ $field->label }}" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label>Type</label>
                            <select name="fields[{{$key}}][field_type]" class="form-control type-select">
                                <option @if($field->field_type == "short_text") selected @endif value="short_text">Short text</option>
                                <option @if($field->field_type == "paragraph") selected @endif value="paragraph">Paragraph</option>
                                <option @if($field->field_type == "radio") selected @endif value="radio">Multiple choice (radio)</option>
                                <option @if($field->field_type == "checkbox") selected @endif value="checkbox">Checkboxes</option>
                                <option @if($field->field_type == "dropdown") selected @endif value="dropdown">Dropdown</option>
                                <option @if($field->field_type == "date") selected @endif value="date">Date</option>
                                <option @if($field->field_type == "number") selected @endif value="number">Number</option>
                                <option @if($field->field_type == "email") selected @endif value="email">Email</option>
                            </select>
                        </div>
                        <div class="mb-2 options-area" @if(!is_null($field->options)) style="display:block;" @else style="display:none;" @endif>
                            <label>Options (pipe separated, e.g. Yes|No|Maybe)</label>
                            <input name="fields[{{$key}}][options]" value="{{ !is_null($field->options) ? implode("|", $field->options) : '' }}" class="form-control mb-1">
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="mb-2 form-check">
                                <input type="checkbox" class="form-check-input" name="fields[{{$key}}][is_required]" value="1" @if($field->is_required == 1) selected @endif>
                                <label class="form-check-label">Required</label>
                            </div>
                            <x-button
                                type='button'
                                class="btn-icon btn-danger btn-sm remove-field"
                                icon="bi bi-trash-fill remove-field"
                                name=""
                                title="Delete field"
                            />
                        </div>
                    </div>
                @endforeach
            @endisset
        </div>

        {{-- Buttons --}}
        <div class="modal-footer">
            <x-button
                type='button'
                class="btn-secondary btn-round"
                icon="bi bi-plus-lg"
                name="Add Field"
                id="addField"
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

<script>
    document.getElementById('addField').addEventListener('click', function () {
        const idx = document.querySelectorAll('.field-block').length;
        const html = `
            <div class="card mb-2 field-block p-3">
                <div class="mb-2">
                    <label>Label</label>
                    <input name="fields[${idx}][label]" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Type</label>
                    <select name="fields[${idx}][field_type]" class="form-control type-select">
                        <option value="short_text">Short text</option>
                        <option value="paragraph">Paragraph</option>
                        <option value="radio">Multiple choice (radio)</option>
                        <option value="checkbox">Checkboxes</option>
                        <option value="dropdown">Dropdown</option>
                        <option value="date">Date</option>
                        <option value="number">Number</option>
                        <option value="email">Email</option>
                    </select>
                </div>
                <div class="mb-2 options-area" style="display:none;">
                    <label>Options (pipe separated, e.g. Yes|No|Maybe)</label>
                    <input name="fields[${idx}][options]" class="form-control">
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="mb-2 form-check">
                        <input type="checkbox" class="form-check-input" name="fields[${idx}][is_required]" value="1">
                        <label class="form-check-label">Required</label>
                    </div>
                    <x-button
                        type='button'
                        class="btn-icon btn-danger btn-sm remove-field"
                        icon="bi bi-trash-fill remove-field"
                        name=""
                        title="Delete field"
                    />
                </div>
            </div>
            `;
        document.getElementById('fields').insertAdjacentHTML('beforeend', html);
    });

    document.addEventListener('click', function(e){
        if(e.target && e.target.classList.contains('remove-field')){
            e.target.closest('.field-block').remove();
        }
    });

    document.addEventListener('change', function(e){
        if(e.target && e.target.classList.contains('type-select')){
            const block = e.target.closest('.field-block');
            const optArea = block.querySelector('.options-area');
            if(['radio','checkbox','dropdown'].includes(e.target.value)){
                optArea.style.display = 'block';
            } else {
                optArea.style.display = 'none';
            }
        }
    });
</script>

