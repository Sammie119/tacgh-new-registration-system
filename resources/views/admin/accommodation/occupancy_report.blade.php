@extends('layouts.app')

@section('title', 'TAC-GH | Accommodation Occupancy')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="Accommodation Occupancy" />

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    @forelse($residences as $residence)
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">{{ $residence->name }}</h5>

                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th class="no-sort">#</th>
                                        <th>Block</th>
                                        <th>Capacity</th>
                                        <th>Occupied</th>
                                        <th>Vacant</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($residence->blocks as $key => $block)
                                        <tr>
                                            <td style="width: 40px">{{ ++$key }}</td>
                                            <td>{{ $block->name }}</td>
                                            <td>{{ $block->capacity }}</td>
                                            <td>{{ $block->occupied }}</td>
                                            <td>{{ $block->vacant }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">No Blocks Found</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @empty
                        <div class="card">
                            <div class="card-body">
                                No Accommodation Found
                            </div>
                        </div>
                    @endforelse

                </div>
            </div>
        </section>

    </main><!-- End #main -->
@endsection
