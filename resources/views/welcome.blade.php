<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TAC-GH Registration | Welcome</title>
    <link rel="shortcut icon" href="{{ asset('dist/assets/img/favicon.ico') }}" type="image/ico">

    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.css') }}"><!--end::Required Plugin(AdminLTE)--><!-- apexcharts -->
</head>
<style>
    .divider:after,.divider:before {
        content: "";
        flex: 1;
        height: 1px;
        background: #eee;
    }
    .h-custom {
        height: calc(100% - 73px);
    }
    @media (max-width: 450px) {
        .h-custom {
            height: 100%;
        }
    }

    #forgot-password {
        text-decoration: none;
    }

    #forgot-password:hover {
        text-decoration: underline;
    }
</style>

<body style="background-color: #fbfcfc">
<main>
    <section class="vh-100">
        <div class="container-fluid h-custom">
            <div class="row d-flex justify-content-center align-items-center h-100">
                <div class="col-md-9 col-lg-6 col-xl-5">
                    <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-login-form/draw2.webp"
                         class="img-fluid" alt="Sample image">
                </div>
                <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
{{--                    <form method="POST" action="{{ route('login') }}">--}}
                        @csrf

                        <div class="divider d-flex align-items-center my-4">
                            <p class="text-center fw-bold mx-1 mb-0"><h3>Yet To Register</h3></p>
                        </div>

                        <div class="text-center text-lg-start mt-4 pt-2">
                            <a href="{{ route('registrant.registration') }}" class="btn btn-dark btn-block w-100 rounded-pill" style="padding-left: 2.5rem; padding-right: 2.5rem; font-weight: bolder; height: 3rem; font-size: 1.2rem">
                                {{ __('Click to Proceed') }}
                            </a>
                        </div>

                        <div class="divider d-flex align-items-center my-4">
                            <p class="text-center fw-bold mx-1 mb-0"><h3>Already Registered</h3></p>
                        </div>

                        <div class="text-center text-lg-start mt-4 pt-2">
                            <a href="/registrant_login" class="btn btn-primary btn-block w-100 rounded-pill" style="padding-left: 2.5rem; padding-right: 2.5rem; font-weight: bolder; height: 3rem; font-size: 1.2rem">
                                {{ __('Log in') }}
                            </a>
                        </div>

{{--                    </form>--}}
                </div>
            </div>
        </div>
    </section>
</main>
</body>
</html>


