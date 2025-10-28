@extends("layouts.guest")

@section('content')
    <main class="" style="margin-top: 80px">
        <div class="container-fluid">
            <h3>Batch Registration Process</h3>

            <x-notify-error :messages="$errors->all()" />
            <x-notify-error :messages="Session::get('success')" :type="1"/>
            <x-notify-error :messages="Session::get('error')" :type="2"/>

            <table class="table datatable">
                <thead>
                    <tr>
                        <th class="no-sort">#</th>
                        <th>Name</th>
                        <th>Reg. #</th>
                        <th>gender</th>
                        <th>date_of_birth</th>
                        <th>phone_number</th>
                        <th>attendance_type</th>
                        <th>token</th>
                        <th>confirmed</th>
                        <th>fees</th>
                        <th>Amount</th>
                        <th>Room</th>
                        <th class="no-sort">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <form action="{{ route('batch_payment') }}" method="post">
                        @csrf
                        <?php $total_fee = 0; $total_amount_paid = 0; ?>
                        @forelse($batch as $key => $registrant)
                            @php
                                $confirmed_registrant = \App\Models\Registrant::where('stage_id', $registrant->id)->first();
                                $amount_paid = \App\Models\Admin\OnlinePayment::where('reg_id', $registrant->id)->sum('amount_paid');
                                $total_fee += $confirmed_registrant->total_fee ?? 0;
                                $total_amount_paid += $amount_paid ?? 0;
                            @endphp

                            <tr class="venue_{{ $registrant->id }}">
                                <td style="width: 50px">{{ ++$key }}</td>
                                <td>{{ event_registrant_name($registrant->id)  }}</td>
                                <td>{{ $confirmed_registrant->registration_no ?? 'NULL' }}</td>
                                <td>{{ get_dropdown_name($registrant->gender) }}</td>
                                <td>{{ $registrant->date_of_birth }}</td>
                                <td>{{ $registrant->phone_number }}</td>
                                <td>{{ $registrant->attendance_type }}</td>
                                <td>{{ $registrant->token }}</td>
                                <td>{{ $registrant->confirmed }}</td>
                                <td>{{ number_format($confirmed_registrant->total_fee ?? 0, 2) }}</td>
                                {{--                        <td>{{ number_format($amount_paid, 2) }}</td>--}}
                                <td width="110px">
                                    <input type="hidden" name="reg[{{ $key }}][registrant_id]" value="{{ $registrant->id }}">
                                    <input type="number" class="form-control amount"
                                       value="{{ number_format($amount_paid, 2) }}"
                                       @if($amount_paid > 0) readonly @endif
                                       step="0.01"
                                       min="0"
                                       required
                                       name="reg[{{ $key }}][amount_paid]"
                                    >
                                </td>
                                <td>{{ get_room_number($confirmed_registrant->room_no ?? 0) }}</td>
                                @if($amount_paid <= 0)
                                    <td style="width: 90px">
                                        <x-button
                                            type='button'
                                            class="btn-icon btn-primary btn-sm"
                                            icon="bi bi-pencil-square"
                                            name=""
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmation"
                                            title="Edit"
                                            data-bs-url="/registrant/batch/confirmation/{{ $registrant->id }}"
                                        />
                                        <x-button
                                            type='button'
                                            class="btn-icon btn-danger btn-sm"
                                            icon="bi bi-trash-fill"
                                            name=""
                                            title="Delete"
                                            onclick="deleteFunction(
                                            {{ $registrant->id }},
                                            'venue',
                                            '/execute_form/delete/venue/{{ $registrant->id }}'
                                        )"
                                        />
                                    </td>
                                @else
                                    <td></td>
                                    <td></td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="50">No Data Found</td>
                            </tr>
                        @endforelse
                        <tr>
{{--                            {{dd(floatval($total_amount_paid))}}--}}
                            <th colspan="9">TOTAL</th>
                            <th>{{ number_format( $total_fee, 2) }}</th>
                            @if($amount_paid > 0)
                                <th>
                                    {{ number_format($total_amount_paid, 2) }}
                                </th>
                                <td></td>
                                <td></td>
                            @else
                                <td width="110px" colspan="2">
                                    <input type="hidden" name="total_fee_to_pay" value="{{ $total_fee }}">
                                    <input type="hidden" name="batch_id" value="{{ $get_data->id }}">
                                    <input type="hidden" name="batch" value="batch">
                                    <input type="number" class="form-control"
                                           value="{{ floatval($total_amount_paid) }}"
                                           step="0.01"
                                           min="0"
                                           required
                                           name="total_amount_paid"
                                           id="total"
                                    >
                                </td>
                                <td><button class="btn btn-primary">Pay</button></td>
                            @endif

                        </tr>
                    </form>
                </tbody>
            </table>
            <!-- End Table with stripped rows -->
        </div>

    </main>

    <!-- Modal -->
    <div class="modal fade" id="confirmation" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl"> {{--modal-fullscreen--}}
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Registrant Confirmation</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
{{--                    @include("registrant.batch_confirmation")--}}
                </div>
            </div>
        </div>
    </div>

    <script>
        const exampleModal = document.getElementById('confirmation')
        if (exampleModal) {
            exampleModal.addEventListener('show.bs.modal', event => {
                // Button that triggered the modal
                const button = event.relatedTarget

                // Ajax Request
                const url = button.getAttribute('data-bs-url')
                $.get(`${url}`, function(result) {
                    $(".modal-body").html(result);
                })
            })
        }
    </script>

    <script>
        function updateTotal() {
            let total = 0;

            // Get all input fields with class "amount"
            document.querySelectorAll('.amount').forEach(input => {
                let value = parseFloat(input.value) || 0; // ignore empty
                total += value;
            });

            document.getElementById('total').value = total.toFixed(2);
        }

        // Listen for typing or deletion in all amount fields
        document.querySelectorAll('.amount').forEach(input => {
            input.addEventListener('input', updateTotal);
        });
    </script>
@endsection
