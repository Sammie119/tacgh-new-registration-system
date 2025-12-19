@extends("layouts.guest")

<style>
    .error-message {
        color: red;
        display: none;
        font-size: 0.9em;
    }
</style>
{{--{{ dd(App::isLocal()) }}--}}
@section('content')
    <main class="" style="margin-top: 80px">
        <div class="container">
            <x-notify-error :messages="$errors->all()" />
            <x-notify-error :messages="Session::get('success')" :type="1"/>
            <x-notify-error :messages="Session::get('error')" :type="2"/>
            @php
                $confirmed = App\Models\Registrant::where('stage_id', $registrant->id)->first();
            @endphp
            @if(!$confirmed)
                <h3>Individual Registration Process</h3>
                <section class="section">
                    <div class="row">
                        <form action="{{ route('registrant.confirm') }}" method="post" onsubmit="return validatePhone();">
                            @csrf
                            <input type="hidden" value="{{ $registrant->id }}" name="id" >
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Personal Information</h5>
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <x-input-select
                                                    :options="$title"
                                                    :selected="$registrant->title"
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
                                                    value="{{ $registrant->first_name }}"
                                                />
                                            </div>
                                            <div class="col-md-3">
                                                <x-input-text
                                                    type="text"
                                                    name="other_names"
                                                    required=""
                                                    label="Other Names"
                                                    value="{{ $registrant->other_names }}"
                                                />
                                            </div>
                                            <div class="col-md-3">
                                                <x-input-text
                                                    type="text"
                                                    name="surname"
                                                    required="true"
                                                    label="Surname"
                                                    value="{{ $registrant->surname }}"
                                                />
                                            </div>

                                            <div class="col-3">
                                                <x-input-select
                                                    :options="$gender"
                                                    :selected="$registrant->gender"
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
                                                    value="{{ $registrant->date_of_birth }}"
                                                />
                                            </div>
                                            <div class="col-3">
                                                <x-input-select
                                                    :options="$marital_status"
                                                    :selected="$registrant->marital_status"
                                                    name="marital_status"
                                                    :type="0"
                                                    required="true"
                                                    label="Marital Status"
                                                />
                                            </div>
                                            <div class="col-3">
                                                <x-input-select
                                                    :options="$nations"
                                                    :selected="$registrant->nationality_id"
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
                                                    value="{{ $registrant->phone_number }}"
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
                                                    value="{{ $registrant->whatsapp_number }}"
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
                                                    value="{{ $registrant->email }}"
                                                />
                                            </div>
                                            <div class="col-md-3">
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
                                            <div class="col-md-3">
                                                <x-input-select
                                                    :options="$position_held"
                                                    :selected="$registrant->position_held"
                                                    name="position_held"
                                                    :type="0"
                                                    required="true"
                                                    label="Position Held"
                                                />
                                            </div>
                                            <div class="col-md-3">
                                                <x-input-select
                                                    :options="$profession"
                                                    :selected="$registrant->profession"
                                                    name="profession"
                                                    :type="0"
                                                    required="true"
                                                    label="Profession"
                                                />
                                            </div>
                                            <div class="col-md-3">
                                                <x-input-select
                                                    :options="$nations"
                                                    :selected="$registrant->residence_country_id"
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
                                                    value="{{ $registrant->languages_spoken }}"
                                                />
                                            </div>

                                            <div class="col-md-3">
                                                <x-input-text
                                                    type="text"
                                                    name="emergency_contacts_name"
                                                    required="true"
                                                    label="Emergency Contact Person"
                                                    value="{{ $registrant->emergency_contacts_name }}"
                                                />
                                            </div>
                                            <div class="col-md-3">
                                                <x-input-text
                                                    type="text"
                                                    name="emergency_contacts_relationship"
                                                    required="true"
                                                    label="Emergency Contact Relationship"
                                                    value="{{ $registrant->emergency_contacts_relationship }}"
                                                />
                                            </div>
                                            <div class="col-md-3">
                                                <x-input-text
                                                    type="tel"
                                                    name="emergency_contacts_phone_number"
                                                    required="true"
                                                    label="Emergency Contact Phone Number"
                                                    value="{{ $registrant->emergency_contacts_phone_number }}"
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
                                                    :selected="$registrant->attendance_type"
                                                    name="attendance_type"
                                                    :type="1"
                                                    :values="['In-Person', 'Online']"
                                                    required="true"
                                                    label="Attendance Type"
                                                />
                                            </div>

                                            <div class="col-md-3">
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
                                            <div class="col-md-3">
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
                                            <div class="col-md-6">
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
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <x-input-select
                                                    :options="$accommodation"
                                                    :selected="0"
                                                    name="accommodation_fee"
                                                    :type="0"
                                                    required="true"
                                                    label="Accommodation Fee"
                                                />
                                            </div>
                                            <div class="col-md-4">
                                                <x-input-select
                                                    :options="$registration"
                                                    :selected="0"
                                                    name="registration_fee"
                                                    :type="0"
                                                    required="true"
                                                    label="Registration Fee"
                                                />
                                            </div>
                                            <div class="col-md-4">
                                                <x-input-text
                                                    type="number"
                                                    name="amount_to_pay"
                                                    required="true"
                                                    label="Amount to Pay"
                                                    value=""
                                                    step="0.01"
                                                    min="0"
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
            @else
                <h3>Individual Registration Confirmed</h3>
                <form action="{{ route('registrant.update') }}" method="post">
                    @csrf
                    <input type="hidden" value="{{ $registrant->id }}" name="reg_id" >
                    <section class="section">
                        <div class="row">
                            <div class="col-lg-6">

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Profile</h5>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Attribute</th>
                                                    <th scope="col">Information</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th scope="row">1</th>
                                                    <td>Name</td>
                                                    <td>{{ event_registrant_name ($registrant->id) }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">2</th>
                                                    <td>Gender</td>
                                                    <td>{{ get_dropdown_name($registrant->gender) }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">3</th>
                                                    <td>Date of Birth</td>
                                                    <td>{{ $registrant->date_of_birth }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">4</th>
                                                    <td>Marital Status</td>
                                                    <td>{{ get_dropdown_name($registrant->marital_status) }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">5</th>
                                                    <td>Nationality</td>
                                                    <td>{{ get_country($registrant->nationality_id) }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">6</th>
                                                    <td>Phone Number</td>
                                                    <td>{{ $registrant->phone_number }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">7</th>
                                                    <td>Whatsapp Number</td>
                                                    <td>{{ $registrant->whatsapp_number }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">8</th>
                                                    <td>Email</td>
                                                    <td>{{ $registrant->email }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">9</th>
                                                    <td>Address</td>
                                                    <td>{{ $registrant->address }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">10</th>
                                                    <td>Position Held</td>
                                                    <td>{{ get_dropdown_name($registrant->position_held) }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">11</th>
                                                    <td>Profession</td>
                                                    <td>{{ get_dropdown_name($registrant->profession) }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">12</th>
                                                    <td>Residence Country</td>
                                                    <td>{{ get_country($registrant->residence_country_id) }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">13</th>
                                                    <td>languages_spoken</td>
                                                    <td>{{ $registrant->languages_spoken }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">14</th>
                                                    <td>Need Accommodation</td>
                                                    <td>{{ $registrant->need_accommodation ? "Yes" : "No" }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">15</th>
                                                    <td>Emergency Contacts Name</td>
                                                    <td>{{ $registrant->emergency_contacts_name }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">16</th>
                                                    <td>Emergency Contacts Relationship</td>
                                                    <td>{{ $registrant->emergency_contacts_relationship }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">17</th>
                                                    <td>Emergency Contact Phone Number</td>
                                                    <td>{{ $registrant->emergency_contacts_phone_number }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">18</th>
                                                    <td>Attendance Type</td>
                                                    <td>{{ $registrant->attendance_type }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">19</th>
                                                    <td>Event</td>
                                                    <td>{{ get_event($registrant->event_id)->name }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">20</th>
                                                    <td>Disability</td>
                                                    <td>{{ $registrant->disability ? "Yes" : "No" }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">21</th>
                                                    <td>Special Needs</td>
                                                    <td>{{ $registrant->special_needs }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">22</th>
                                                    <td>Confirmed</td>
                                                    <td>{{ $registrant->confirmed }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">23</th>
                                                    <td>token</td>
                                                    <td>{{ $registrant->token }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">24</th>
                                                    <td>batch_no</td>
                                                    <td>{{ $registrant->batch_no }}</td>
                                                </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>

                            <div class="col-lg-6">

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Accommodation & Fees</h5>

                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Attribute</th>
                                                <th scope="col">Information</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th scope="row">1</th>
                                                    <td>Name</td>
                                                    <td>{{ event_registrant_name ($registrant->id) }}</td>
                                                </tr>

                                                <tr>
                                                    <th scope="row">2</th>
                                                    <td>registration_no</td>
                                                    <td>{{ $confirmed_registrant->registration_no }}</td>
                                                </tr>

                                                <tr>
                                                    <th scope="row">5</th>
                                                    <td>Accommodation Type</td>
                                                    <td>
                                                        <select name="accommodation_fee" class="form-control">
                                                            @foreach ($accommodation as $item)
                                                                <option @if($confirmed_registrant->accommodation_type == $item->id) selected @endif value="{{ $item->id }}">{{ $item->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">7</th>
                                                    <td>Registration Type</td>
                                                    <td>
                                                        <select name="registration_fee" class="form-control">
                                                            @foreach ($registration as $item)
                                                                <option @if($confirmed_registrant->registration_type == $item->id) selected @endif value="{{ $item->id }}">{{ $item->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">6</th>
                                                    <td>Accommodation Fee</td>
                                                    <td>{{ $confirmed_registrant->accommodation_fee }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">8</th>
                                                    <td>Registration Fee</td>
                                                    <td>{{ $confirmed_registrant->registration_fee }}</td>
                                                </tr>

                                                <tr>
                                                    <th scope="row">9</th>
                                                    <td>Total Fee</td>
                                                    <td>{{ $confirmed_registrant->total_fee }}</td>
                                                </tr>

                                                <tr>
                                                    <th scope="row">10</th>
                                                    <td>Room Number</td>
                                                    <td>{{ get_room_number($confirmed_registrant->room_no) }}</td>
                                                </tr>

                                                <tr>
                                                    <th scope="row">11</th>
                                                    <td>Check-In</td>
                                                    <td>{{ $confirmed_registrant->check_in }}</td>
                                                </tr>

                                                <tr>
                                                    <th scope="row">12</th>
                                                    <td>Check-Out</td>
                                                    <td>{{ $confirmed_registrant->check_out }}</td>
                                                </tr>

                                            </tbody>
                                        </table>
                                        <div style="text-align: right">
                                            <x-button
                                                type='submit'
                                                class="btn-success rounded-pill"
                                                icon="bi bi-save2"
                                                name="Submit"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Payment Information</h5>

                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th scope="col">#</th>
                                                    <th scope="col">Mode</th>
                                                    <th scope="col">Amount</th>
                                                    <th scope="col">Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $total = 0;
                                                @endphp
                                                @forelse($payments as $key => $payment)
                                                    <tr>
                                                        <th scope="row">{{ ++$key }}</th>
                                                        <td>{{ $payment->payment_mode }}</td>
                                                        <td>{{ number_format($payment->amount_paid, 2) }}</td>
                                                        <td>{{ $payment->date_paid }}</td>
                                                    </tr>
                                                    @php
                                                        $total += $payment->amount_paid;
                                                    @endphp
                                                @empty
                                                    <tr>
                                                        <td colspan="6">No Data Found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="2">Total</th>
                                                    <th>{{ number_format($total, 2) }}</th>
                                                    <th></th>
                                                </tr>
                                                @if($confirmed_registrant->total_fee > $total)
                                                    <form action="{{ route('make_payment') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="stage_id" value="{{ $registrant->id }}">
                                                        <tr>
                                                            <th colspan="2">Pay Remainder</th>
                                                            <th><input type="number" name="total_fee" value="{{ number_format($confirmed_registrant->total_fee - $total, 2) }}" class="form-control"/></th>
                                                            <th><button class="btn btn-primary">Pay</button></th>
                                                        </tr>
                                                    </form>
                                                @endif
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </section>
                </form>
            @endif
        </div>
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
@endsection
