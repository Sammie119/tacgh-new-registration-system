@extends('layouts.app')

@section('title', 'TAC-GH | Batch Room Allocation')

@section('content')
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Batch Room Allocation</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Batch Room Allocation</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <x-notify-error :messages="$errors->all()" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Batches</h5>

                            <table class="table">
                                <thead>
                                <tr>
                                    <th class="no-sort">#</th>
                                    <th>Batch No.</th>
                                    <th>Coordinator Email</th>
                                    <th>Coordinator Phone</th>
                                    <th>Needing Accommodation</th>
                                    <th>Eligible Now</th>
                                    <th class="no-sort">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($batches as $key => $batch)
                                    @php
                                        $needingCount = $needing_accommodation_counts[$batch->batch_no] ?? 0;
                                        $eligibleCount = $eligible_counts[$batch->batch_no] ?? 0;
                                    @endphp
                                    <tr>
                                        <td style="width: 40px">{{ ++$key }}</td>
                                        <td>{{ $batch->batch_no }}</td>
                                        <td>{{ $batch->email }}</td>
                                        <td>{{ $batch->phone_number }}</td>
                                        <td>{{ $needingCount }}</td>
                                        <td>{{ $eligibleCount }}</td>
                                        <td style="width: 160px">
                                            @if($eligibleCount > 0)
                                                <form method="POST" action="{{ route('batch_room_allocation.assign') }}">
                                                    @csrf
                                                    <input type="hidden" name="batch_no" value="{{ $batch->batch_no }}">
                                                    <x-button
                                                        type="submit"
                                                        class="btn-success btn-sm"
                                                        icon="ri-hotel-bed-fill fs-5"
                                                        name="Assign Rooms"
                                                        title="Assign rooms to every eligible registrant in this batch"
                                                    />
                                                </form>
                                            @else
                                                <span class="text-muted">&mdash;</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7">No Data Found</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>

                            {{ $batches->links() }}
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main><!-- End #main -->
@endsection
