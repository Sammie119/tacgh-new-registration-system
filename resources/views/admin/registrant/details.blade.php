<div>
    <div class="row">
        <div class="col-lg-6">
            <h6>Profile</h6>
            <table class="table table-sm">
                <tbody>
                <tr>
                    <th>Name</th>
                    <td>{{ event_registrant_name($registrant->id) }}</td>
                </tr>
                <tr>
                    <th>Registration No.</th>
                    <td>{{ $confirmed_registrant->registration_no ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Gender</th>
                    <td>{{ get_dropdown_name($registrant->gender) }}</td>
                </tr>
                <tr>
                    <th>Date of Birth</th>
                    <td>{{ $registrant->date_of_birth }}</td>
                </tr>
                <tr>
                    <th>Marital Status</th>
                    <td>{{ get_dropdown_name($registrant->marital_status) }}</td>
                </tr>
                <tr>
                    <th>Nationality</th>
                    <td>{{ get_country($registrant->nationality_id) }}</td>
                </tr>
                <tr>
                    <th>Phone Number</th>
                    <td>{{ $registrant->phone_number }}</td>
                </tr>
                <tr>
                    <th>WhatsApp Number</th>
                    <td>{{ $registrant->whatsapp_number }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $registrant->email }}</td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td>{{ $registrant->address }}</td>
                </tr>
                <tr>
                    <th>Position Held</th>
                    <td>{{ get_dropdown_name($registrant->position_held) }}</td>
                </tr>
                <tr>
                    <th>Profession</th>
                    <td>{{ get_dropdown_name($registrant->profession) }}</td>
                </tr>
                <tr>
                    <th>Residence Country</th>
                    <td>{{ get_country($registrant->residence_country_id) }}</td>
                </tr>
                <tr>
                    <th>Languages Spoken</th>
                    <td>{{ $registrant->languages_spoken }}</td>
                </tr>
                <tr>
                    <th>Need Accommodation</th>
                    <td>{{ $registrant->need_accommodation ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <th>Emergency Contact Name</th>
                    <td>{{ $registrant->emergency_contacts_name }}</td>
                </tr>
                <tr>
                    <th>Emergency Contact Relationship</th>
                    <td>{{ $registrant->emergency_contacts_relationship }}</td>
                </tr>
                <tr>
                    <th>Emergency Contact Phone</th>
                    <td>{{ $registrant->emergency_contacts_phone_number }}</td>
                </tr>
                <tr>
                    <th>Attendance Type</th>
                    <td>{{ $registrant->attendance_type }}</td>
                </tr>
                <tr>
                    <th>Disability</th>
                    <td>{{ $registrant->disability ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <th>Special Needs</th>
                    <td>{{ $registrant->special_needs }}</td>
                </tr>
                <tr>
                    <th>Confirmed</th>
                    <td>{{ $registrant->confirmed }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="col-lg-6">
            <h6>Accommodation & Fees</h6>
            <table class="table table-sm">
                <tbody>
                <tr>
                    <th>Accommodation Type</th>
                    <td>{{ $fee_type_names[$confirmed_registrant->accommodation_type ?? null] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Registration Fee Type</th>
                    <td>{{ $fee_type_names[$confirmed_registrant->registration_type ?? null] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Total Fee</th>
                    <td>{{ number_format($confirmed_registrant->total_fee ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <th>Room</th>
                    <td>{{ get_room_number($confirmed_registrant->room_no ?? null) }}</td>
                </tr>
                <tr>
                    <th>Check-In</th>
                    <td>{{ $confirmed_registrant->check_in ?? null }}</td>
                </tr>
                <tr>
                    <th>Check-Out</th>
                    <td>{{ $confirmed_registrant->check_out ?? null }}</td>
                </tr>
                </tbody>
            </table>

            <h6>Payment History</h6>
            <table class="table table-sm">
                <thead>
                <tr>
                    <th>Amount Paid</th>
                    <th>Mode</th>
                    <th>Date Paid</th>
                </tr>
                </thead>
                <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>{{ number_format($payment->amount_paid, 2) }}</td>
                        <td>{{ $payment->payment_mode }}</td>
                        <td>{{ $payment->date_paid }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No Data Found</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-footer">
        <x-button
            type='button'
            class="btn-danger btn-round"
            icon="bi bi-x-lg"
            name="Close"
            data-bs-dismiss="modal"
        />
    </div>
</div>
