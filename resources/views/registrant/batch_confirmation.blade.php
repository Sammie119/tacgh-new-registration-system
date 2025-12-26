<section class="section">
    <div class="row">
        <form action="{{ route('batch.confirm') }}" method="post">
            @csrf
            <input type="hidden" value="{{ $registrant->id }}" name="id" >
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Personal Information</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <x-input-select
                                    :options="$title"
                                    :selected="$registrant->title"
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
                                    value="{{ $registrant->first_name }}"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-input-text
                                    type="text"
                                    name="other_names"
                                    required=""
                                    label="Other Names"
                                    value="{{ $registrant->other_names }}"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-input-text
                                    type="text"
                                    name="surname"
                                    required="true"
                                    label="Surname"
                                    value="{{ $registrant->surname }}"
                                />
                            </div>

                            <div class="col-md-4">
                                <x-input-select
                                    :options="$gender"
                                    :selected="$registrant->gender"
                                    name="gender"
                                    :type="0"
                                    required="true"
                                    label="Gender"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-input-text
                                    type="date"
                                    name="date_of_birth"
                                    required="true"
                                    label="Date of Birth"
                                    value="{{ $registrant->date_of_birth }}"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-input-select
                                    :options="$marital_status"
                                    :selected="$registrant->marital_status"
                                    name="marital_status"
                                    :type="0"
                                    required="true"
                                    label="Marital Status"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-input-select
                                    :options="$nations"
                                    :selected="$registrant->nationality_id"
                                    name="nationality_id"
                                    :type="0"
                                    required="true"
                                    label="Nationality"
                                />
                            </div>

                            <div class="col-md-4">
                                <x-input-text
                                    type="tel"
                                    name="phone_number"
                                    required="true"
                                    label="Phone Number"
                                    value="{{ $registrant->phone_number }}"
                                    placeholder="+233541234567"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-input-text
                                    type="tel"
                                    name="whatsapp_number"
                                    required=""
                                    label="WhatsApp Number"
                                    value="{{ $registrant->whatsapp_number }}"
                                    placeholder="+233541234567"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-input-text
                                    type="email"
                                    name="email"
                                    required="true"
                                    label="Email"
                                    value="{{ $registrant->email }}"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-input-select
                                    :options="['Yes', 'No']"
                                    :selected="$registrant->need_accommodation"
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
                                    value="{{ $registrant->address }}"
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
                            <div class="col-md-4">
                                <x-input-select
                                    :options="$position_held"
                                    :selected="$registrant->position_held"
                                    name="position_held"
                                    :type="0"
                                    required="true"
                                    label="Position Held"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-input-select
                                    :options="$profession"
                                    :selected="$registrant->profession"
                                    name="profession"
                                    :type="0"
                                    required="true"
                                    label="Profession"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-input-select
                                    :options="$nations"
                                    :selected="$registrant->residence_country_id"
                                    name="residence_country_id"
                                    :type="0"
                                    required="true"
                                    label="Country of Resident"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-input-text
                                    type="text"
                                    name="languages_spoken"
                                    required="true"
                                    label="Languages Spoken"
                                    value="{{ $registrant->languages_spoken }}"
                                />
                            </div>

                            <div class="col-md-4">
                                <x-input-text
                                    type="text"
                                    name="emergency_contacts_name"
                                    required="true"
                                    label="Emergency Contact Person"
                                    value="{{ $registrant->emergency_contacts_name }}"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-input-text
                                    type="text"
                                    name="emergency_contacts_relationship"
                                    required="true"
                                    label="Emergency Contact Relationship"
                                    value="{{ $registrant->emergency_contacts_relationship }}"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-input-text
                                    type="tel"
                                    name="emergency_contacts_phone_number"
                                    required="true"
                                    label="Emergency Contact Phone Number"
                                    value="{{ $registrant->emergency_contacts_phone_number }}"
                                    placeholder="+233541234567"
                                />
                            </div>
                            <div class="col-md-4">
                                <x-input-select
                                    :options="['In-Person', 'Online']"
                                    :selected="$registrant->attendance_type"
                                    name="attendance_type"
                                    :type="1"
                                    :values="['In-Person', 'Online']"
                                    required="true"
                                    label="Attendance Type"
                                />
                            </div>

                            <div class="col-md-4">
                                <input type="hidden" value="{{ $registrant->event_id }}" name="event_id" >
                                <x-input-select
                                    :options="$events"
                                    :selected="$registrant->event_id"
                                    name="event_id"
                                    :type="0"
                                    required="true"
                                    label="Event Attending"
                                    disabled
                                />
                            </div>
                            <div class="col-md-4">
                                <x-input-select
                                    :options="['Yes', 'No']"
                                    :selected="$registrant->disability"
                                    name="disability"
                                    :type="1"
                                    :values="[1, 0]"
                                    required="true"
                                    label="Disabled?"
                                />
                            </div>
                            <div class="col-md-8">
                                <x-input-text
                                    type="text"
                                    name="special_needs"
                                    required="true"
                                    label="Have any special needs"
                                    value="{{ $registrant->special_needs }}"
                                />
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-12">

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Accommodation & Events Fees</h5>
{{--                        {{ dd($confirmed_registrant, $accommodation, $confirmed_registrant->accommodation_type) }}--}}
                        <div class="row g-3">
                            <div class="col-md-6">
                                <x-input-select
                                    :options="$accommodation"
                                    :selected="isset($confirmed_registrant) ? $confirmed_registrant->accommodation_type : 0"
                                    name="accommodation_fee"
                                    :type="0"
                                    required="true"
                                    label="Accommodation Fee"
                                />
                            </div>
                            <div class="col-md-6">
                                <x-input-select
                                    :options="$registration"
                                    :selected="isset($confirmed_registrant) ? $confirmed_registrant->registration_type : 0"
                                    name="registration_fee"
                                    :type="0"
                                    required="true"
                                    label="Registration Fee"
                                />
                            </div>
                            <input type="hidden" value="0.00" name="amount_to_pay">

                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <x-button
                    type='button'
                    class="btn-danger rounded-pill"
                    icon="bi bi-x-lg"
                    name="Close"
                    data-bs-dismiss="modal"
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
