@extends('layouts.app')

@section('title', 'TAC-GH | Registration Demographics')

@section('content')
    <main id="main" class="main">

        <x-breadcrumbs page="Registration Demographics" />

        <section class="section">
            <div class="row">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Registrants</h5>
                            <div class="d-flex align-items-center">
                                <h6>{{ $total }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Confirmed</h5>
                            <div class="d-flex align-items-center">
                                <h6>{{ $confirmed_counts['Yes'] ?? 0 }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Pending</h5>
                            <div class="d-flex align-items-center">
                                <h6>{{ $confirmed_counts['No'] ?? 0 }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Gender</h5>
                            <canvas id="genderChart" style="max-height: 300px;"></canvas>
                            <script>
                                document.addEventListener("DOMContentLoaded", () => {
                                    new Chart(document.querySelector('#genderChart'), {
                                        type: 'doughnut',
                                        data: {
                                            labels: [
                                                @foreach($gender_counts as $genderId => $count)
                                                    '{{ $dropdown_names[$genderId] ?? 'Unknown' }}',
                                                @endforeach
                                            ],
                                            datasets: [{
                                                label: 'Gender',
                                                data: [
                                                    @foreach($gender_counts as $count)
                                                        {{ $count }},
                                                    @endforeach
                                                ],
                                                backgroundColor: ['rgb(255, 99, 132)', 'rgb(54, 162, 235)', 'rgb(255, 205, 86)', 'rgb(75, 192, 192)'],
                                                hoverOffset: 4
                                            }]
                                        }
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Age Brackets</h5>
                            <canvas id="ageChart" style="max-height: 300px;"></canvas>
                            <script>
                                document.addEventListener("DOMContentLoaded", () => {
                                    new Chart(document.querySelector('#ageChart'), {
                                        type: 'bar',
                                        data: {
                                            labels: [
                                                @foreach($age_brackets as $label => $count)
                                                    '{{ $label }}',
                                                @endforeach
                                            ],
                                            datasets: [{
                                                label: 'Registrants',
                                                data: [
                                                    @foreach($age_brackets as $count)
                                                        {{ $count }},
                                                    @endforeach
                                                ],
                                                backgroundColor: 'rgb(54, 162, 235)'
                                            }]
                                        }
                                    });
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Nationality</h5>
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Nationality</th>
                                    <th>Count</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($nationality_counts as $nationalityId => $count)
                                    <tr>
                                        <td>{{ $country_names[$nationalityId] ?? 'Unknown' }}</td>
                                        <td>{{ $count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2">No Data Found</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Country of Residence</h5>
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Country</th>
                                    <th>Count</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($residence_counts as $countryId => $count)
                                    <tr>
                                        <td>{{ $country_names[$countryId] ?? 'Unknown' }}</td>
                                        <td>{{ $count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2">No Data Found</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Attendance Type</h5>
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Count</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($attendance_counts as $type => $count)
                                    <tr>
                                        <td>{{ $type }}</td>
                                        <td>{{ $count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2">No Data Found</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main><!-- End #main -->
@endsection
