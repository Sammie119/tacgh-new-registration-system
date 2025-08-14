<style>
    span.ccode{
        width: 10px;
        margin-left: 5px;
        padding: 0px 10px;
        height: 10px;
    }
    span.legend{
        margin-left: 5px;
        margin-right: 10px;
    }
</style>
@if($rooms->count() > 0)
    <table class="table table-bordered" style="border: none; border-color: #f4f4f4">
        <tr>
            <td colspan="{{ count($rooms) }}">
                <div class="d-flex align-items-center justify-content-center">
                    <span>Legend: </span>
                    <div>
                        <span class="ccode bg-primary"></span>
                        <span class="legend">Regular</span>
                    </div>
                    <div>
                        <span class="ccode bg-secondary"></span>
                        <span class="legend">Blocked</span>
                    </div>
                    <div>
                        <span class="ccode bg-info"></span>
                        <span class="legend">Full</span>
                    </div>
                </div>

            </td>
        </tr>
        <tr>
            <th style="text-align: center;width: 40px;" width='50px' >
                Floor
            </th>
            <th style="text-align: center" colspan="{{ count($rooms) }}">Rooms ( Showing Room No. and Total Occupants )</th>
        </tr>
{{--        Accommodation: status --}}
{{--        AccommodationBlock: status--}}
{{--        AccommodationRoom: assign--}}
        @php
            $resident_status = \App\Models\Admin\Accommodation::find($block->residence_id)->status;
            $block_status = $block->status;
//            {{ dd($resident_status, $block_status); }}
        @endphp

        @for($i = 1; $i <= $block->total_floors; $i++)
            <tr style="font-size: 15px">
                <td style="text-align: center">{{ $i }}</td>
                @foreach ($rooms->where('floor_no', $i)->sortBy('room_no') as $key => $value)
                    @php
                        $assigned_roommates = get_total_room_occupants ($value->id, 1);//Change 5 to event_id..
                        $color = '';

                       if(($resident_status == 'Blocked') || ($block_status == 'Blocked') || ($value->assign == 0))
                           $color = 'bg-secondary';
                       elseif(($assigned_roommates == $value->total_occupants))
                            $color = 'bg-info';
                        else
                            $color = 'bg-primary';
                    @endphp
                        <td style= "min-width: 100px" class="rooms-col {{ $color }}" align="center">
                            <a href="{{ route('room', [$value->id]) }}" style="transform: none" class="text-white">
                                Rm {{$value->prefix}} {{ $value->room_no }} {{$value->suffix}}
                                <br>
                                <div><i class="ri-hotel-bed-fill fs-4"></i></div> ({{ $assigned_roommates }} of {{ $value->total_occupants }})
                            </a>
                        </td>
                @endforeach
            </tr>
        @endfor
    </table>
@else
    <form method="POST" action="{{ route('rooms') }}">
        @csrf

        <input type="hidden" value="{{ $block->id }}" name="block_id" />
        <input type="hidden" value="{{ $block->total_rooms }}" name="total_rooms" />
        <input type="hidden" value="{{ $block->residence_id }}" name="residence_id" />
        <table class="table">
            <thead>
                <tr>
                    <th>Floor</th>
                    <th colspan="2">Rooms Range</th>
                    <th>Prefix</th>
                    <th>Suffix</th>
                    <th>Average beds per room</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 1; $i <= $block->total_floors; $i++)
                    <tr class="align-middle block_{{ $i }}">
                        <td>{{ $i }}</td>
                        <td>
                            <input type="hidden" value="{{ $i }}" name="rooms[{{ $i }}][floor_no]" />
                            <input type="number" class="form-control" name="rooms[{{ $i }}][room_no_from]" placeholder="From" required />
                        </td>
                        <td>
                            <input type="number" class="form-control" name="rooms[{{ $i }}][room_no_to]" placeholder="To" required />
                        </td>
                        <td>
                            <input type="text" class="form-control" name="rooms[{{ $i }}][prefix]" placeholder="Eg. (A)100" />
                        </td>
                        <td>
                            <input type="text" class="form-control" name="rooms[{{ $i }}][suffix]" placeholder="Eg. 100(A)" />
                        </td>
                        <td>
                            <input type="number" class="form-control" name="rooms[{{ $i }}][beds_per_room]" placeholder="Beds" required />
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>

        {{-- Buttons --}}
        <div class="modal-footer">
            <x-button
                type='button'
                class="btn-danger btn-round"
                icon="bi bi-x-lg"
                name="Close"
                data-bs-dismiss="modal"
            />
            <x-button
                type='submit'
                class="btn-success btn-round"
                icon="bi bi-save2"
                name="Submit"
            />
        </div>
    </form>
@endif



