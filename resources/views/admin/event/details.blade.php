<div>
    <table class="table table-sm">
        <tbody>
        <tr>
            <th>Name</th>
            <td>{{ $event->name }}</td>
        </tr>
        <tr>
            <th>Description</th>
            <td>{{ $event->description }}</td>
        </tr>
        <tr>
            <th>Prefix</th>
            <td>{{ $event->code_prefix }}</td>
        </tr>
        <tr>
            <th>Start Date</th>
            <td>{{ $event->start_date }}</td>
        </tr>
        <tr>
            <th>End Date</th>
            <td>{{ $event->end_date }}</td>
        </tr>
        <tr>
            <th>Venue</th>
            <td>{{ $venue?->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Payment Required</th>
            <td>{{ $event->is_payment_required }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ $event->status }}</td>
        </tr>
        <tr>
            <th>Active</th>
            <td>{{ $event->active_flag ? 'Yes' : 'No' }}</td>
        </tr>
        </tbody>
    </table>

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
