@extends("layouts.guest")

<style>
    .error-message {
        color: red;
        display: none;
        font-size: 0.9em;
    }
    .logo img {
        max-height: 50px !important;
    }
</style>

@section('content')
    <main class="container">
        <div class="d-flex justify-content-center py-4">
            <a class="logo d-flex align-items-center w-auto">
                <img src="{{ asset("assets/img/logo3.png") }}" alt="">
            </a>
        </div><!-- End Logo -->
        <div class="pagetitle mb-4">
            <h1>Registration Form</h1>
        </div><!-- End Page Title -->

        <x-notify-error :messages="$errors->all()" />

        <!-- Default Accordion -->
        <div class="accordion" id="accordionExample">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        Individual Registration Form
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                    <div class="accordion-body">

                        {{-- Returning-registrant lookup - deliberately outside
                             #individualRegistrationForm so none of it is submitted. --}}
                        <div class="card" id="previousRegistrantLookup">
                            <div class="card-body">
                                <h5 class="card-title">Registered before?</h5>
                                <p class="small text-muted mb-3">Enter the phone number or email you used before to fill in some of your details, then check them and complete the rest.</p>
                                <div class="row g-2 align-items-center">
                                    <div class="col-md-6">
                                        <input type="text" class="form-control" id="lookup_identifier" placeholder="Phone number or email" autocomplete="off">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-outline-primary w-100" id="lookupFindBtn">Find me</button>
                                    </div>
                                </div>
                                <div id="lookupMessage" class="small mt-2"></div>
                                <div id="lookupResultsWrapper" class="row g-2 align-items-center mt-1" style="display: none;">
                                    <div class="col-md-6">
                                        <select class="form-control" id="lookupResults"></select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-primary w-100" id="lookupUseBtn">Use these details</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <section class="section">
                            <div class="row">
                                <form id="individualRegistrationForm" action="{{ route('registrant.store') }}" method="post" onsubmit="return validatePhone();">
                                    @csrf
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Personal Information</h5>
                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <x-input-select
                                                            :options="$title"
                                                            :selected="0"
                                                            name="title"
                                                            :type="0"
                                                            required="true"
                                                            label="Title"
                                                        />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <x-input-text
                                                            type="text"
                                                            name="first_name"
                                                            required="true"
                                                            label="First Name"
                                                            value=""
                                                        />
                                                    </div>
                                                    <div class="col-md-4">
                                                        <x-input-text
                                                            type="text"
                                                            name="surname"
                                                            required="true"
                                                            label="Surname"
                                                            value=""
                                                        />
                                                    </div>

                                                    <div class="col-3">
                                                        <x-input-select
                                                            :options="$gender"
                                                            :selected="0"
                                                            name="gender"
                                                            :type="0"
                                                            required="true"
                                                            label="Gender"
                                                        />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <x-input-text
                                                            type="date"
                                                            name="date_of_birth"
                                                            required="true"
                                                            label="Date of Birth"
                                                            value=""
                                                        />
                                                    </div>
                                                    <div class="col-3">
                                                        <x-input-select
                                                            :options="$marital_status"
                                                            :selected="0"
                                                            name="marital_status"
                                                            :type="0"
                                                            required="true"
                                                            label="Marital Status"
                                                        />
                                                    </div>
                                                    <div class="col-3">
                                                        <x-input-select
                                                            :options="$nations"
                                                            :selected="0"
                                                            name="nationality_id"
                                                            :type="0"
                                                            required="true"
                                                            label="Nationality"
                                                        />
                                                    </div>

                                                    <div class="col-md-3">
                                                        <x-input-text
                                                            type="tel"
                                                            name="phone_number"
                                                            required="true"
                                                            label="Phone Number"
                                                            value=""
                                                            placeholder="0248000000"
                                                            {{--                                            pattern="^(0[0-9]{9}|\+233[0-9]{9})$"--}}
                                                            class="phoneInput"
                                                            oninput="clearError(1)"
                                                        />
                                                        <div class="error-message" id="errorMsg">
                                                            Please enter a valid Ghanaian phone number (e.g., 0248000000).
                                                        </div><br>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <x-input-text
                                                            type="email"
                                                            name="email"
                                                            required="true"
                                                            label="Email"
                                                            value=""
                                                        />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <x-input-select
                                                            :options="['Yes', 'No']"
                                                            :selected="3"
                                                            name="need_accommodation"
                                                            :type="1"
                                                            :values="[1, 0]"
                                                            required="true"
                                                            label="Need Accommodation"
                                                        />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <x-input-select
                                                            :options="['Yes', 'No']"
                                                            :selected="3"
                                                            name="is_student"
                                                            :type="1"
                                                            :values="[1, 0]"
                                                            required="true"
                                                            label="Is Student"
                                                            onchange="toggleInstitutionName(this)"
                                                        />
                                                    </div>
                                                    <div class="col-md-9" id="institutionNameWrapper" style="display:none">
                                                        <x-input-text
                                                            type="text"
                                                            name="institution_name"
                                                            label="Institution Name"
                                                            value=""
                                                        />
                                                    </div>

                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="col-12">

                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Other Information</h5>
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <x-input-select
                                                            :options="$profession"
                                                            :selected="0"
                                                            name="profession"
                                                            :type="0"
                                                            required="true"
                                                            label="Profession"
                                                        />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <x-input-select
                                                            :options="$nations"
                                                            :selected="0"
                                                            name="residence_country_id"
                                                            :type="0"
                                                            required="true"
                                                            label="Country of Residence"
                                                        />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <x-input-text
                                                            type="text"
                                                            name="languages_spoken"
                                                            required="true"
                                                            label="Languages Spoken"
                                                            value=""
                                                        />
                                                    </div>

                                                    <div class="col-md-3">
                                                        <x-input-text
                                                            type="text"
                                                            name="emergency_contacts_name"
                                                            required="true"
                                                            label="Emergency Contact Person"
                                                            value=""
                                                        />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <x-input-text
                                                            type="tel"
                                                            name="emergency_contacts_phone_number"
                                                            required="true"
                                                            label="Emergency Contact Phone Number"
                                                            value=""
                                                            placeholder="0248000000"
                                                            class="phoneInput"
                                                            oninput="clearError(3)"
                                                        />
                                                        <div class="error-message" id="errorMsg3">
                                                            Please enter a valid Ghanaian phone number (e.g., 0248000000).
                                                        </div><br>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <x-input-select
                                                            :options="['Yes', 'No']"
                                                            :selected="3"
                                                            name="disability"
                                                            :type="1"
                                                            :values="[1, 0]"
                                                            required="true"
                                                            label="Disabled?"
                                                        />
                                                    </div>
                                                    <div class="col-md-6">
                                                        <x-input-text
                                                            type="text"
                                                            name="special_needs"
                                                            required="true"
                                                            label="Have any special needs"
                                                            value=""
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="modal-footer">
                                        <x-button
                                            type='button'
                                            class="btn-danger rounded-pill"
                                            icon="bi bi-arrow-left"
                                            name="Back"
                                            onclick="window.location.href='/'"
                                        />
                                        <x-button
                                            type='submit'
                                            class="btn-success rounded-pill"
                                            icon="bi bi-save2"
                                            name="Submit"
                                        />
                                    </div>
                                </form>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        Batch Registration Upload
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                    <div class="accordion-body">

                        <section class="section">
                            <div class="row">
                                <form id="batchUploadForm" action="{{ route('registrant.batch') }}" method="post" onsubmit="return validatePhone2();" enctype="multipart/form-data">
                                    @csrf
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Batch Information</h5>
                                                <div class="row g-3">
                                                    <a href="{{ route('registrant_download') }}" class="btn btn-link btn-flat">Click here to download excel template</a>
                                                    <div class="col-md-3">
                                                        <x-input-text
                                                            type="email"
                                                            name="email"
                                                            required="true"
                                                            label="Email"
                                                            value=""
                                                        />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <x-input-text
                                                            type="tel"
                                                            name="phone_number"
                                                            required="true"
                                                            label="Phone Number"
                                                            value=""
                                                            placeholder="0248000000"
                                                            class="phoneInput2"
                                                            oninput="clearError2(1)"
                                                        />
                                                        <div class="error-message" id="errorMsgg">
                                                            Please enter a valid Ghanaian phone number (e.g., 0248000000).
                                                        </div><br>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <x-input-text
                                                            type="tel"
                                                            name="whatsapp_number"
                                                            required=""
                                                            label="WhatsApp Number"
                                                            value=""
                                                            placeholder="0248000000"
                                                            class="phoneInput2"
                                                            oninput="clearError2(2)"
                                                        />
                                                        <div class="error-message" id="errorMsgg2">
                                                            Please enter a valid Ghanaian phone number (e.g., 0248000000).
                                                        </div><br>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <x-input-text
                                                            type="file"
                                                            name="file"
                                                            required="true"
                                                            label=""
                                                            value=""
                                                        />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <x-input-select
                                                            :options="['Yes', 'No']"
                                                            :selected="3"
                                                            name="is_student"
                                                            :type="1"
                                                            :values="[1, 0]"
                                                            required="true"
                                                            label="Is Student"
                                                            onchange="toggleBatchInstitutionName(this)"
                                                        />
                                                    </div>
                                                    <div class="col-md-9" id="batchInstitutionNameWrapper" style="display:none">
                                                        <x-input-text
                                                            type="text"
                                                            name="institution_name"
                                                            label="Institution Name"
                                                            value=""
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="modal-footer">
                                        <x-button
                                            type='button'
                                            class="btn-danger rounded-pill"
                                            icon="bi bi-arrow-left"
                                            name="Back"
                                            onclick="window.location.href='/'"
                                        />
                                        <x-button
                                            id="batchUploadSubmitBtn"
                                            type='submit'
                                            class="btn-success rounded-pill"
                                            icon="bi bi-save2"
                                            name="Submit"
                                        />
                                    </div>
                                </form>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div><!-- End Default Accordion Example -->
    </main>

    <script>
        function validatePhone() {
            const phoneInput = document.querySelectorAll('.phoneInput');
            const errorMsg = document.getElementById('errorMsg');
            const errorMsg3 = document.getElementById('errorMsg3');
            const regex = /^(0[0-9]{9}|\+233[0-9]{9})$/;

            if (!regex.test(phoneInput[0].value)) {
                errorMsg.style.display = 'block';
                if (!regex.test(phoneInput[1].value)) {
                    errorMsg3.style.display = 'block';
                }
                return false;
            }
            if (!regex.test(phoneInput[1].value)) {
                errorMsg3.style.display = 'block';
                return false;
            }
            return true;
        }

        function clearError(id) {
            if(id === 1)
                document.getElementById('errorMsg').style.display = 'none';
            else
                document.getElementById('errorMsg3').style.display = 'none';
        }

        // Institution Name only matters (and is only required) when Is
        // Student is "Yes" - keep it hidden/optional otherwise, and clear
        // any stale value so a "No" submission never carries one along.
        function toggleInstitutionName(select) {
            const wrapper = document.getElementById('institutionNameWrapper');
            const input = wrapper.querySelector('input[name="institution_name"]');
            const isYes = select.value === '1';
            wrapper.style.display = isYes ? '' : 'none';
            input.required = isYes;
            if (!isYes) input.value = '';
        }
        document.addEventListener('DOMContentLoaded', function () {
            // Scoped to this form specifically - the batch upload form
            // below has its own, separate "Is Student" select with the
            // same field name.
            toggleInstitutionName(document.querySelector('#individualRegistrationForm select[name="is_student"]'));
        });

        // Returning-registrant lookup: fetch masked matches for a phone/email,
        // then fill only the basic fields the server returns. Field lookups
        // are scoped to #individualRegistrationForm - the batch form below
        // reuses several of the same ids. Deferred to DOMContentLoaded because
        // the guest layout loads jQuery after this section.
        document.addEventListener('DOMContentLoaded', function () {
            let matches = [];
            const $message = $('#lookupMessage');
            const $wrapper = $('#lookupResultsWrapper');
            const $results = $('#lookupResults');

            function showMessage(text, isError) {
                $message.text(text).toggleClass('text-danger', !!isError).toggleClass('text-muted', !isError);
            }

            $('#lookupFindBtn').on('click', function () {
                const identifier = $('#lookup_identifier').val().trim();
                $wrapper.hide();
                if (!identifier) {
                    showMessage('Enter a phone number or email.', true);
                    return;
                }

                const $btn = $(this).prop('disabled', true);
                showMessage('Searching...', false);

                $.post('{{ route('registrant.lookup') }}', {_token: '{{ csrf_token() }}', identifier: identifier})
                    .done(function (data) {
                        matches = data || [];
                        if (!matches.length) {
                            showMessage('No previous registration found. Please fill in the form below.', false);
                            return;
                        }
                        $results.empty();
                        matches.forEach(function (match, i) {
                            $results.append($('<option>').val(i).text(match.label));
                        });
                        showMessage('Select yourself from the list.', false);
                        $wrapper.show();
                    })
                    .fail(function (xhr) {
                        showMessage(xhr.status === 429
                            ? 'Too many searches. Please wait a minute and try again.'
                            : 'Could not search right now. Please fill in the form below.', true);
                    })
                    .always(function () {
                        $btn.prop('disabled', false);
                    });
            });

            $('#lookupUseBtn').on('click', function () {
                const match = matches[$results.val()];
                if (!match) return;

                const $form = $('#individualRegistrationForm');
                Object.entries(match.fields).forEach(function ([name, value]) {
                    const $field = $form.find('[name="' + name + '"]');
                    if (value === null || value === undefined) return;
                    if ($field.is('select') && !$field.find('option[value="' + value + '"]').length) return;
                    $field.val(String(value));
                });

                // The typed identifier came from the registrant themselves,
                // so it's safe to carry across into the matching field.
                const identifier = $('#lookup_identifier').val().trim();
                $form.find(identifier.includes('@') ? '[name="email"]' : '[name="phone_number"]').val(identifier);

                showMessage('Details filled in. Please check them and complete the remaining fields.', false);
                $wrapper.hide();
            });
        });

    </script>

    <script>
        function validatePhone2() {
            const phoneInput2 = document.querySelectorAll('.phoneInput2');
            const errorMsgg = document.getElementById('errorMsgg');
            const errorMsgg2 = document.getElementById('errorMsgg2');
            const regex = /^(0[0-9]{9}|\+233[0-9]{9})$/;

            if (!regex.test(phoneInput2[0].value)) {
                errorMsgg.style.display = 'block';
                if (!regex.test(phoneInput2[1].value)) {
                    errorMsgg2.style.display = 'block';
                }
                return false;
            }
            if (!regex.test(phoneInput2[1].value)) {
                errorMsgg2.style.display = 'block';
                return false;
            }
            return true;
        }

        function clearError2(id) {
            if(id === 1)
                document.getElementById('errorMsgg').style.display = 'none';
            else
                document.getElementById('errorMsgg2').style.display = 'none';
        }

        // Is Student/Institution Name here apply to the WHOLE batch (every
        // registrant in the uploaded file), entered once by the
        // coordinator - same show/hide behavior as the individual form's
        // toggleInstitutionName(), just against this form's own elements.
        function toggleBatchInstitutionName(select) {
            const wrapper = document.getElementById('batchInstitutionNameWrapper');
            const input = wrapper.querySelector('input[name="institution_name"]');
            const isYes = select.value === '1';
            wrapper.style.display = isYes ? '' : 'none';
            input.required = isYes;
            if (!isYes) input.value = '';
        }
        document.addEventListener('DOMContentLoaded', function () {
            toggleBatchInstitutionName(document.querySelector('#batchUploadForm select[name="is_student"]'));
        });

        // Prevent a double-click/double-tap on a slow upload from importing
        // the whole batch file twice - there's no server-side dedup on this
        // action, so a resubmit creates fully duplicate registrants.
        document.getElementById('batchUploadForm').addEventListener('submit', function (e) {
            if (!e.defaultPrevented) {
                document.getElementById('batchUploadSubmitBtn').disabled = true;
            }
        });

    </script>


@endsection
