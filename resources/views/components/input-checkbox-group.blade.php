@props(['options' => [], 'selected' => [], 'required' => false, 'name', 'label'])
@php
    $selectedValues = array_map('strval', (array) $selected);
@endphp
@once
    <style>
        /* Checkbox select: looks like a select, opens a menu of checkboxes and
           shows the choices as removable chips (Atlassian CheckboxSelect style). */
        .cbs { position: relative; }
        .cbs-control {
            position: relative; display: flex; align-items: center; gap: .5rem;
            min-height: calc(3.5rem + 2px); padding: .375rem .5rem .375rem .75rem;
            cursor: pointer; background-color: #fff;
        }
        /* Label floats inside the box like the neighbouring form-floating
           inputs: centred when empty, small at the top once something is picked. */
        .cbs-caption {
            position: absolute; left: .75rem; top: 50%; transform: translateY(-50%);
            color: #212529; pointer-events: none; transition: all .1s ease-in-out;
        }
        .cbs.has-value .cbs-control { padding-top: 1.6rem; padding-bottom: .3rem; }
        .cbs.has-value .cbs-caption { top: .3rem; transform: none; font-size: .85em; opacity: .65; }
        .cbs-control:focus, .cbs-control.show { border-color: #86b7fe; box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .25); outline: 0; }
        .cbs-values { display: flex; flex-wrap: wrap; gap: .25rem; flex: 1 1 auto; min-width: 0; }
        .cbs-chip {
            display: inline-flex; align-items: center; gap: .125rem;
            background: #e9ecef; color: #212529; border-radius: .25rem;
            font-size: .875rem; line-height: 1.5; padding: 0 0 0 .4rem;
        }
        .cbs-chip-remove, .cbs-clear {
            border: 0; background: transparent; color: #6c757d; line-height: 1;
            padding: .15rem .35rem; border-radius: .25rem;
        }
        .cbs-chip-remove:hover { background: #ffd5d2; color: #ae2a19; }
        .cbs-clear:hover { color: #212529; }
        .cbs-indicators { display: flex; align-items: center; gap: .25rem; color: #6c757d; flex: 0 0 auto; }
        .cbs-divider { width: 1px; align-self: stretch; background: #dee2e6; margin: .25rem 0; }
        .cbs-menu { max-height: 240px; overflow-y: auto; }
        .cbs-menu .dropdown-item { display: flex; align-items: center; gap: .6rem; cursor: pointer; white-space: normal; }
        .cbs-menu .dropdown-item:has(input:checked) { background: #e7f1ff; }
        .cbs-menu .form-check-input { margin: 0; flex: 0 0 auto; }
        .cbs-proxy { position: absolute; left: 0; bottom: 0; width: 100%; height: 1px; opacity: 0; pointer-events: none; border: 0; padding: 0; }
    </style>
@endonce
<div id="{{ $name }}_group" {!! $attributes->merge(['class' => 'cbs']) !!}>
    <div class="position-relative dropdown">
        <div
            class="form-control cbs-control"
            id="{{ $name }}_control"
            role="button"
            tabindex="0"
            aria-haspopup="listbox"
            aria-label="{{ $label }}"
            aria-expanded="false"
            data-bs-toggle="dropdown"
            data-bs-auto-close="outside"
        >
            <span class="cbs-caption">{{ $label }} <span style="color: red">{{ $required ? '*' : '' }}</span></span>
            <div class="cbs-values"></div>
            <div class="cbs-indicators">
                <button type="button" class="cbs-clear" aria-label="Clear all" style="display: none;">&times;</button>
                <span class="cbs-divider"></span>
                <i class="bi bi-chevron-down px-1"></i>
            </div>
        </div>
        <input type="text" class="cbs-proxy" tabindex="-1" aria-hidden="true" {{ $required ? 'required' : '' }}>
        {{-- Bootstrap 5.1 finds the menu as a sibling of the toggle. --}}
        <div class="dropdown-menu w-100 cbs-menu" role="listbox" aria-multiselectable="true">
            @foreach ($options as $option)
                <label class="dropdown-item" for="{{ $name }}_{{ $option['id'] }}">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="{{ $name }}[]"
                        value="{{ $option['id'] }}"
                        id="{{ $name }}_{{ $option['id'] }}"
                        @checked(in_array((string) $option['id'], $selectedValues, true))
                    >
                    <span>{{ $option['name'] }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>
<script>
    (function () {
        const group = document.getElementById(@json($name.'_group'));
        if (!group || group.dataset.cbsReady) return;
        group.dataset.cbsReady = '1';

        const control = group.querySelector('.cbs-control');
        const values = group.querySelector('.cbs-values');
        const clearBtn = group.querySelector('.cbs-clear');
        const proxy = group.querySelector('.cbs-proxy');
        const boxes = Array.prototype.slice.call(group.querySelectorAll('.cbs-menu input[type="checkbox"]'));
        const required = proxy.required;

        function untick(box) {
            box.checked = false;
            box.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function render() {
            const checked = boxes.filter(function (b) { return b.checked; });
            values.querySelectorAll('.cbs-chip').forEach(function (chip) { chip.remove(); });

            checked.forEach(function (box) {
                const chip = document.createElement('span');
                chip.className = 'cbs-chip';
                const text = document.createElement('span');
                text.textContent = box.nextElementSibling.textContent;
                const remove = document.createElement('button');
                remove.type = 'button';
                remove.className = 'cbs-chip-remove';
                remove.setAttribute('aria-label', 'Remove ' + text.textContent);
                remove.innerHTML = '&times;';
                // Stop the click reaching the dropdown toggle so removing a
                // chip doesn't also open/close the menu.
                remove.addEventListener('click', function (e) {
                    e.stopPropagation();
                    untick(box);
                });
                chip.appendChild(text);
                chip.appendChild(remove);
                values.appendChild(chip);
            });

            group.classList.toggle('has-value', checked.length > 0);
            clearBtn.style.display = checked.length ? '' : 'none';

            // The real checkboxes sit in a hidden menu and can't show the
            // browser's validation bubble, so a transparent proxy under the
            // control carries "required" instead.
            proxy.value = checked.length ? String(checked.length) : '';
            proxy.setCustomValidity(required && !checked.length ? 'Please select at least one ' + @json(strtolower($label)) + '.' : '');
        }

        boxes.forEach(function (b) { b.addEventListener('change', render); });

        clearBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            boxes.forEach(function (b) { if (b.checked) untick(b); });
        });

        // A div toggle doesn't respond to Enter/Space on its own.
        control.addEventListener('keydown', function (e) {
            if ((e.key === 'Enter' || e.key === ' ') && window.bootstrap) {
                e.preventDefault();
                bootstrap.Dropdown.getOrCreateInstance(control).toggle();
            }
        });

        render();
    })();
</script>
