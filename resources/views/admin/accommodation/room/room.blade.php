@extends('layouts.app')

@section('title', "TAC-GH | Rm $room->prefix $room->room_no $room->suffix")

<style>
    label{
        font-weight: bold;
    }
</style>

@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Room {{$room->prefix}} {{ $room->room_no }} {{$room->suffix}}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('venues') }}">Venues</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('accommodations', [$venue_id]) }}">accommodations</a></li>
                    <li class="breadcrumb-item active">Room {{$room->prefix}} {{ $room->room_no }} {{$room->suffix}}</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <x-notify-error :messages="$errors->all()" />

        <section class="section">
            <div class="row" style="font-size: 14px">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Room {{$room->prefix}} {{ $room->room_no }} {{$room->suffix}}</h5>
                            <form action="{{ route('room', [$room->id]) }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-5">
                                        <div class="row mb-3">
                                            <label for="inputText" class="col-4 col-form-label text-end">Name</label>
                                            <div class="col-8">
                                                <input type="text" name="name" value="{{ $room->name }}" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <div class="row mb-3">
                                            <label for="inputText" class="col-5 col-form-label text-end">Room</label>
                                            <div class="col-7">
                                                <input type="text" value="{{ $room->room_no }}" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <div class="row mb-3">
                                            <label for="inputText" class="col-5 col-form-label text-end">Floor</label>
                                            <div class="col-7">
                                                <input type="text" value="{{ $room->floor_no }}" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="row mb-3">
                                            <label for="inputText" class="col-4 col-form-label text-end">Block</label>
                                            <div class="col-8">
                                                <input type="text" value="{{ $room->block->name }}" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-5">
                                        <div class="row mb-3">
                                            <label for="inputText" class="col-4 col-form-label text-end">Residence</label>
                                            <div class="col-8">
                                                <input type="text" value="{{ $room->resident->name }}" class="form-control" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <div class="row mb-3">
                                            <label for="inputText" class="col-5 col-form-label text-end">Prefix</label>
                                            <div class="col-7">
                                                <input type="text" name="prefix" value="{{ $room->prefix }}" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <div class="row mb-3">
                                            <label for="inputText" class="col-5 col-form-label text-end">Suffix</label>
                                            <div class="col-7">
                                                <input type="text" name="suffix" value="{{ $room->suffix }}" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="row mb-3">
                                            <label for="inputText" class="col-4 col-form-label text-end">Assign</label>
                                            <div class="col-8">
                                                <select name="assign" class="form-control">
                                                    <option @if($room->assign == 1)  selected @endif value="1">Active</option>
                                                    <option @if($room->assign == 0) selected @endif value="0">Blocked</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-5">
                                        <div class="row mb-3">
                                            <label for="inputText" class="col-4 col-form-label text-end">Room Type</label>
                                            <div class="col-8">
                                                <select name="type" class="form-control" onchange="room_type_change(this.value)">
                                                    <option @if($room->type == 'Regular')  selected @endif value="Regular">Regular</option>
                                                    <option @if($room->type == 'Reserved') selected @endif value="Reserved">Reserved</option>
                                                    <option @if($room->type == 'Special') selected @endif value="Special">Special</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-2">
                                        <div class="row mb-3">
                                            <label for="inputText" class="col-5 col-form-label text-end">Beds</label>
                                            <div class="col-7">
                                                <input type="text" name="total_occupants" value="{{ $room->total_occupants }}" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <div class="row mb-3">
                                            <label for="inputText" class="col-5 col-form-label text-end">Accommodation Type</label>
                                            <div class="col-7">
                                                <select name="special_acc" class="form-control special_acc" @if($room->special_acc == "0") disabled @endif>
                                                    <option @if($room->special_acc == "0") selected @endif value="0">Regular Accommodation</option>
                                                    @php
                                                        $acc = \App\Models\Admin\Dropdown::where('lookup_code_id', 9)->get()
                                                    @endphp
                                                    @foreach($acc as $ac)
                                                        <option @if($room->special_acc == $ac->id)  selected @endif value="{{ $ac->id }}">{{ $ac->full_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-5">
                                        <div class="row mb-3">
                                            <label for="inputText" class="col-4 col-form-label text-end">Gender</label>
                                            <div class="col-8">
                                                <select name="gender" class="form-control">
                                                    <option @if($room->assign == "M")  selected @endif value="M">Male</option>
                                                    <option @if($room->assign == "F") selected @endif value="F">Female</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="col-8 text-center">
                                            <x-button
                                                type='submit'
                                                class="btn-success btn-round"
                                                icon="bi bi-save2"
                                                name="Submit"
                                            />
                                        </div>
                                    </div>


                                </div>
                            </form>
                        </div>
                    </div>

                </div>


                <div class="col-lg-4">

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Add Roommate here</h5>
                            <form action="{{ route('add_roommate') }}" method="post">
                                @csrf
                                <input type="hidden" name="room_id" value="{{ $room->id }}">
                                <input type="hidden" name="event_id" value="{{ Auth()->user()->event_id }}">
                                <div class="input-group input-group-sm">
                                    <input type="text" placeholder="Registration Number" list="attendee_id" name="registration_no" required class="form-control">
                                    <datalist id="attendee_id">
                                        @foreach($participants as $participant)
                                            <option value="{{ $participant->registration_no }}">{{ event_registrant_name($participant->stage_id) }}</option>
                                        @endforeach
                                    </datalist>
                                    <span class="input-group-btn">
					                      <button type="submit" class="btn btn-info btn-flat">Add Roommate</button>
					                    </span>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Transfer Roommate here</h5>
                            <form action="{{ route('transfer_roommate') }}" method="post">
                                @csrf
                                <input type="hidden" name="room_id" value="{{ $room->id }}">
                                <input type="hidden" name="event_id" value="{{ Auth()->user()->event_id }}">
                                <div class="input-group input-group-sm">
                                    <input type="text" placeholder="Registration Number" list="attendee_id" name="registration_no" required class="form-control">
                                    <span class="input-group-btn">
					                      <button type="submit" class="btn btn-warning btn-flat">Transfer</button>
					                    </span>
                                </div>

                            </form>
                        </div>
                    </div>

                </div>

                <div class="col-lg-8">

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Roommates' Information <small>(Capacity : {{ $room->total_occupants }}) Left : {{ $room->total_occupants - get_total_room_occupants($room->id, Auth()->user()->event_id) }}</small></h5>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Reg. #</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Phone #</th>
                                        <th scope="col">Time</th>
                                        <th scope="col">Del</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roommates as $key => $roommate)
                                        <tr class="roommate_{{ $room->id }}">
                                            @php
                                                $registrant = \App\Models\Registrant::where('stage_id', $roommate->registrant_id)->first();
//                                                dd($registrant_confirm->registration_no);
                                            @endphp
                                            <th scope="row">{{ ++$key }}</th>
                                            <td>{{ $registrant->registration_no }}</td>
                                            <td>{{ event_registrant_name($registrant->stage_id) }}</td>
                                            <td>{{ $registrant->stage->phone_number }}</td>
                                            <td>{{ $registrant->check_in }}</td>
                                            <td>
                                                <x-button
                                                    type='button'
                                                    class="btn-icon btn-danger btn-sm"
                                                    icon="bi bi-trash-fill"
                                                    name=""
                                                    title="Delete"
                                                    onclick="deleteFunction(
                                                            {{ $roommate->id }},
                                                            'roommate',
                                                            '/execute_form/delete/roommate/{{ $roommate->id }}',
                                                            'refresh'
                                                        )"
                                                />
                                            </td>
                                        </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main><!-- End #main -->

    <x-modal />

    <x-modal-toggle />

    <script>
        function room_type_change(value){
            if(value === 'Special'){
                $('.special_acc').prop('disabled', false);
            }else{
                $('.special_acc').prop('disabled', true);
            }
        }
    </script>

@endsection


