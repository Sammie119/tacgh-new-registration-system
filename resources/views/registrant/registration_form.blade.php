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

                        <section class="section">
                            <div class="row">
                                <form action="{{ route('registrant.store') }}" method="post" onsubmit="return validatePhone();">
                                    @csrf
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Personal Information</h5>
                                                <div class="row g-3">
                                                    <div class="col-md-3">
                                                        <x-input-select
                                                            :options="$title"
                                                            :selected="0"
                                                            name="title"
                                                            :type="0"
                                                            required="true"
                                                            label="Title"
                                                        />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <x-input-text
                                                            type="text"
                                                            name="first_name"
                                                            required="true"
                                                            label="First Name"
                                                            value=""
                                                        />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <x-input-text
                                                            type="text"
                                                            name="other_names"
                                                            required=""
                                                            label="Other Names"
                                                            value=""
                                                        />
                                                    </div>
                                                    <div class="col-md-3">
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
                                                            placeholder="+233541234567"
                                                            {{--                                            pattern="^\+[1-9][0-9]{10,}$"--}}
                                                            class="phoneInput"
                                                            oninput="clearError(1)"
                                                        />
                                                        <div class="error-message" id="errorMsg">
                                                            Please enter a valid phone number starting with + and at least 12 digits (e.g., +233541234567).
                                                        </div><br>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <x-input-text
                                                            type="tel"
                                                            name="whatsapp_number"
                                                            required=""
                                                            label="WhatsApp Number"
                                                            value=""
                                                            placeholder="+233541234567"
                                                            class="phoneInput"
                                                            oninput="clearError(2)"
                                                        />
                                                        <div class="error-message" id="errorMsg2">
                                                            Please enter a valid phone number starting with + and at least 12 digits (e.g., +233541234567).
                                                        </div><br>
                                                    </div>
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

                                                    <div class="col-md-12">
                                                        <x-input-text
                                                            type="text"
                                                            name="address"
                                                            required="true"
                                                            label="Address"
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
                                                            :options="$position_held"
                                                            :selected="0"
                                                            name="position_held"
                                                            :type="0"
                                                            required="true"
                                                            label="Position Held"
                                                        />
                                                    </div>
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
                                                            label="Country of Resident"
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
                                                            type="text"
                                                            name="emergency_contacts_relationship"
                                                            required="true"
                                                            label="Emergency Contact Relationship"
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
                                                            placeholder="+233541234567"
                                                            class="phoneInput"
                                                            oninput="clearError(3)"
                                                        />
                                                        <div class="error-message" id="errorMsg3">
                                                            Please enter a valid phone number starting with + and at least 12 digits (e.g., +233541234567).
                                                        </div><br>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <x-input-select
                                                            :options="['In-Person', 'Online']"
                                                            :selected="3"
                                                            name="attendance_type"
                                                            :type="1"
                                                            :values="['In-Person', 'Online']"
                                                            required="true"
                                                            label="Attendance Type"
                                                        />
                                                    </div>

                                                    <div class="col-md-3">
                                                        <x-input-select
                                                            :options="$events"
                                                            :selected="0"
                                                            name="event_id"
                                                            :type="0"
                                                            required="true"
                                                            label="Event Attending"
                                                        />
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
                                <form action="{{ route('registrant.batch') }}" method="post" onsubmit="return validatePhone2();" enctype="multipart/form-data">
                                    @csrf
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Batch Information</h5>
                                                <div class="row g-3">
                                                    <a href="{{ route('registrant_download') }}" class="btn btn-link btn-flat">Click here to download excel template</a>
                                                    <div class="col-md-3">
                                                        <x-input-select
                                                            :options="$events"
                                                            :selected="0"
                                                            name="event_id"
                                                            :type="0"
                                                            required="true"
                                                            label="Event Attending"
                                                        />
                                                    </div>
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
                                                            placeholder="+233541234567"
                                                            class="phoneInput2"
                                                            oninput="clearError2(1)"
                                                        />
                                                        <div class="error-message" id="errorMsgg">
                                                            Please enter a valid phone number starting with + and at least 12 digits (e.g., +233541234567).
                                                        </div><br>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <x-input-text
                                                            type="tel"
                                                            name="whatsapp_number"
                                                            required=""
                                                            label="WhatsApp Number"
                                                            value=""
                                                            placeholder="+233541234567"
                                                            class="phoneInput2"
                                                            oninput="clearError2(2)"
                                                        />
                                                        <div class="error-message" id="errorMsgg2">
                                                            Please enter a valid phone number starting with + and at least 12 digits (e.g., +233541234567).
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
        </div><!-- End Default Accordion Example -->
    </main>

    <script>
        function validatePhone() {
            const phoneInput = document.querySelectorAll('.phoneInput');
            const errorMsg = document.getElementById('errorMsg');
            const errorMsg2 = document.getElementById('errorMsg2');
            const errorMsg3 = document.getElementById('errorMsg3');
            const regex = /^\+[1-9][0-9]{10,}$/;

            if (!regex.test(phoneInput[0].value)) {
                errorMsg.style.display = 'block';
                if (!regex.test(phoneInput[1].value)) {
                    errorMsg2.style.display = 'block';
                }
                if (!regex.test(phoneInput[2].value)) {
                    errorMsg3.style.display = 'block';
                }
                return false;
            }
            if (!regex.test(phoneInput[1].value)) {
                errorMsg2.style.display = 'block';
                if (!regex.test(phoneInput[2].value)) {
                    errorMsg3.style.display = 'block';
                }
                return false;
            }
            if (!regex.test(phoneInput[2].value)) {
                errorMsg3.style.display = 'block';
                return false;
            }
            return true;
        }

        function clearError(id) {
            if(id === 1)
                document.getElementById('errorMsg').style.display = 'none';
            else if(id === 2)
                document.getElementById('errorMsg2').style.display = 'none';
            else
                document.getElementById('errorMsg3').style.display = 'none';
        }

    </script>

    <script>
        function validatePhone2() {
            const phoneInput2 = document.querySelectorAll('.phoneInput2');
            const errorMsgg = document.getElementById('errorMsgg');
            const errorMsgg2 = document.getElementById('errorMsgg2');
            const regex = /^\+[1-9][0-9]{10,}$/;

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

    </script>


@endsection
