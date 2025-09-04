@extends('layouts.app')

@section('content')

    <main id="main" class="main">

        <x-breadcrumbs page="Dashboard" />

        <section class="section dashboard">
            <div class="row">
                <div class="col-12">
                    <div class="row">
                        <!-- Sales Card -->
                        <div class="col-xxl-4 col-md-6">
                            <div class="card info-card sales-card">

                                <div class="filter">
                                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                        <li class="dropdown-header text-start">
                                            <h6>Goto</h6>
                                        </li>

                                        <li><a class="dropdown-item" href="{{ route('all_registrant') }}">Registrants</a></li>
                                    </ul>
                                </div>

                                <div class="card-body">
                                    <h5 class="card-title">Registrants <span>| {{ $reg_stage }}</span></h5>

                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="bi bi-people"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>{{ $confirmed }}</h6>
                                            <span class="text-success small pt-1 fw-bold">{{ ($reg_stage == 0) ? 0 : (($confirmed/$reg_stage) * 100) }}%</span> <span class="text-muted small pt-2 ps-1">Confirmed</span>

                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div><!-- End Sales Card -->

                        <!-- Revenue Card -->
                        <div class="col-xxl-4 col-md-6">
                            <div class="card info-card revenue-card">

                                <div class="filter">
                                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                        <li class="dropdown-header text-start">
                                            <h6>Goto</h6>
                                        </li>

                                        <li><a class="dropdown-item" href="{{ route('payments') }}">Online Payments</a></li>
                                        <li><a class="dropdown-item" href="{{ route('financial_entries') }}">Financial Entries</a></li>
                                    </ul>
                                </div>

                                <div class="card-body">
                                    <h5 class="card-title">Revenue <span>| Total</span></h5>

                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            ₵
                                        </div>
                                        <div class="ps-3">
                                            <h6>GH₵{{ number_format($payments + $revenue, 2) }}</h6>
                                            <span class="text-success small pt-1 fw-bold">Online: {{ number_format($payments, 2) }}</span> <span class="text-success small pt-1 fw-bold">Income: {{ number_format($revenue, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div><!-- End Revenue Card -->

                        <!-- Customers Card -->
                        <div class="col-xxl-4 col-xl-12">

                            <div class="card info-card customers-card">

                                <div class="filter">
                                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                        <li class="dropdown-header text-start">
                                            <h6>Goto</h6>
                                        </li>

                                        <li><a class="dropdown-item" href="{{ route('allocate_room') }}">Rooms</a></li>
                                    </ul>
                                </div>

                                <div class="card-body">
                                    <h5 class="card-title">Residents <span>| Beds</span></h5>

                                    <div class="d-flex align-items-center">
                                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                            <i class="ri-hotel-bed-fill fs-1"></i>
                                        </div>
                                        <div class="ps-3">
                                            <h6>{{ $total_beds }}</h6>
                                            <span class="text-danger small pt-1 fw-bold">{{ ($total_beds == 0) ? 0 : (($beds_occupied/$total_beds) * 100) }}%</span> <span class="text-muted small pt-2 ps-1">Occupied</span>

                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div><!-- End Customers Card -->
                    </div>
                </div>

                <!-- Left side columns -->
                <div class="col-lg-8">
                    <div class="row">
                        <!-- Reports -->
                        <div class="col-6">
                            <div class="card">
                                <div class="filter">
                                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                        <li class="dropdown-header text-start">
                                            <h6>Goto</h6>
                                        </li>

                                        <li><a class="dropdown-item" href="{{ route('all_registrant') }}">Registration</a></li>
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">Registration</h5>

                                    <!-- Doughnut Chart -->
                                    <canvas id="doughnutChart" style="max-height: 400px;"></canvas>
                                    <script>
                                        document.addEventListener("DOMContentLoaded", () => {
                                            new Chart(document.querySelector('#doughnutChart'), {
                                                type: 'doughnut',
                                                data: {
                                                    labels: [
                                                        'Male',
                                                        'Female'
                                                    ],
                                                    datasets: [{
                                                        label: 'Gender',
                                                        data: [
                                                            {{ $total_males }},
                                                            {{ $confirmed - $total_males }}
                                                        ],
                                                        backgroundColor: [
                                                            'rgb(255, 99, 132)',
                                                            'rgb(54, 162, 235)'
                                                        ],
                                                        hoverOffset: 4
                                                    }]
                                                }
                                            });
                                        });
                                    </script>
                                    <!-- End Doughnut CHart -->

                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card">
                                <div class="filter">
                                    <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                        <li class="dropdown-header text-start">
                                            <h6>Goto</h6>
                                        </li>

                                        <li><a class="dropdown-item" href="{{ route('payments') }}">Online Payments</a></li>
                                        <li><a class="dropdown-item" href="{{ route('financial_entries') }}">Financial Entries</a></li>
                                    </ul>
                                </div>

                                <div class="card-body">
                                    <h5 class="card-title">Revenue</h5>

                                    <!-- Pie Chart -->
                                    <canvas id="pieChart" style="max-height: 400px;"></canvas>
                                    <script>
                                        document.addEventListener("DOMContentLoaded", () => {
                                            new Chart(document.querySelector('#pieChart'), {
                                                type: 'pie',
                                                data: {
                                                    labels: [
                                                        'Online Payments',
                                                        'Income'
                                                    ],
                                                    datasets: [{
                                                        label: 'Revenue',
                                                        data: [
                                                            {{ $payments }},
                                                            {{ $revenue }}
                                                        ],
                                                        backgroundColor: [
                                                            'rgb(54, 162, 235)',
                                                            'rgb(255, 205, 86)'
                                                        ],
                                                        hoverOffset: 4
                                                    }]
                                                }
                                            });
                                        });
                                    </script>
                                    <!-- End Pie CHart -->

                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- End Left side columns -->

                <!-- Right side columns -->
                <div class="col-lg-4">

                    <!-- Recent Activity -->
                    <div class="card">
                        <div class="filter">
                            <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <li class="dropdown-header text-start">
                                    <h6>Goto</h6>
                                </li>

                                <li><a class="dropdown-item" href="{{ route('downloads') }}">Downloads</a></li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <h5 class="card-title">Downloads <span>| Documents</span></h5>

                            <div class="activity">
                                @forelse($downloads as $download)
                                    <div class="activity-item d-flex">
                                        <div class="activite-label">{{ $download->download_count }} dwlds</div>
                                        <i class='bi bi-circle-fill activity-badge text-success align-self-start'></i>
                                        <div class="activity-content">
                                            <a href="{{ route('registrant.download.file', $download->id) }}" class="fw-bold text-dark">{{ $download->file_name }}</a>
                                        </div>
                                    </div><!-- End activity item-->
                                @empty
                                    <div class="activity-item d-flex">
                                        <div class="activite-label">0 dwlds</div>
                                        <i class='bi bi-circle-fill activity-badge text-success align-self-start'></i>
                                        <div class="activity-content">
                                            No Data Found
                                        </div>
                                    </div><!-- End activity item-->
                                @endforelse
                            </div>

                        </div>
                    </div><!-- End Recent Activity -->

                </div><!-- End Right side columns -->

            </div>
        </section>

    </main><!-- End #main -->

@endsection


