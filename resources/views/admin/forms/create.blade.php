
<div class="container">
    <form method="POST" action="{{ route('forms.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input name="title" class="form-control" required value="{{ old('title') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control">{{ old('description') }}</textarea>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="is_public" name="is_public" checked>
            <label class="form-check-label" for="is_public">Public (anyone with link can respond)</label>
        </div>

        <h4>Fields</h4>
        <hr>
        <div id="fields">

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

