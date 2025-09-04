<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
        <a href="{{ route('dashboard') }}" class="logo d-flex align-items-center">
            <img src="{{ asset("assets/img/logo5.png") }}" alt="">
        </a>
{{--        <i class="bi bi-list toggle-sidebar-btn"></i>--}}
    </div><!-- End Logo -->

    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center">

            <li class="nav-item d-block d-lg-none">
                <a class="nav-link nav-icon search-bar-toggle " href="#">
                    <i class="bi bi-search"></i>
                </a>
            </li><!-- End Search Icon-->

            @php
                $auth = get_registrant_login();
                if(empty($auth->first_name)){
                    $auth->first_name = 'Batch';
                    $auth->surname = 'Registrant';
                }
            @endphp

            @if(!empty($auth->first_name))
                <li class="nav-item">
                    <a href="#" class="nav-link nav-profile d-flex align-items-center"
                       data-bs-toggle="modal"
                       data-bs-target="#exampleModal"
                    >Downloads</a>
                </li>
            @endif

            <li class="nav-item dropdown pe-3">

                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <img src="{{ asset("assets/img/profile-img.png") }}" alt="Profile" class="rounded-circle">
                    <span class="d-none d-md-block dropdown-toggle ps-2">{{ $auth->first_name. " ". $auth->surname }}</span>
                </a><!-- End Profile Iamge Icon -->

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6>{{ $auth->first_name. " ". $auth->surname }}</h6>
                        <span>Role: Registrant</span>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('registrant_logout') }}">
                            @csrf

                            <a class="dropdown-item d-flex align-items-center" href="{{ route('registrant_logout') }}"
                               onclick="event.preventDefault();
                                    this.closest('form').submit();"
                            >
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Sign Out</span>
                            </a>
                        </form>
                    </li>

                </ul><!-- End Profile Dropdown Items -->
            </li><!-- End Profile Nav -->

        </ul>
    </nav><!-- End Icons Navigation -->

</header><!-- End Header -->

<!-- Modal -->
<div class="modal fade" id="exampleModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Downloadable Documents</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include("registrant.downloads")
            </div>
        </div>
    </div>
</div>
