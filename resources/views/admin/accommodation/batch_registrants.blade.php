<div>
    <h5>Registrants in Batch {{ $batch_no }}</h5>

    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Confirmed</th>
                <th>Status</th>
                <th>Room</th>
            </tr>
            </thead>
            <tbody>
            @forelse($registrants as $key => $registrant)
                @php
                    $confirmed = $confirmed_registrants->get($registrant->id);
                    $roomName = $confirmed?->room_no ? get_room_number($confirmed->room_no) : null;

                    $paid = $paid_totals[$registrant->id] ?? 0;
                    $approvedValue = (int) ($approved_totals[$registrant->id] ?? 1);
                    $fullyPaid = $confirmed && $confirmed->total_fee > 0 && $paid >= $confirmed->total_fee;
                @endphp
                <tr>
                    <td>{{ ++$key }}</td>
                    <td>{{ event_registrant_name($registrant->id) }}</td>
                    <td>{{ $registrant->confirmed }}</td>
                    <td>
                        @if($fullyPaid)
                            <span class="badge bg-success">Full</span>
                        @elseif($approvedValue === 2)
                            <span class="badge bg-primary">Approved</span>
                        @else
                            <span class="badge bg-danger">Disapproved</span>
                        @endif
                    </td>
                    <td>
                        @if($roomName)
                            {{ $roomName }}
                        @else
                            <span class="text-muted">Not Assigned</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No Data Found</td>
                </tr>
            @endforelse
            </tbody>
        </table>
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
