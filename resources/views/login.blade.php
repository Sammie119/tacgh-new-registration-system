<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TAC-GH Registration | Login</title>
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

                    <x-notify-error :messages="$errors->all()" />
                    <x-notify-error :messages="Session::get('success')" :type="1"/>
                    <x-notify-error :messages="Session::get('error')" :type="2"/>

                    <form method="POST" action="{{ route('registrant_login') }}">
                        @csrf

                        <div class="divider d-flex align-items-center my-4">
                            <p class="text-center fw-bold mx-1 mb-0"><h3>Log In</h3></p>
                        </div>

                        <!-- Email input -->
                        <div data-mdb-input-init class="form-outline mb-4">
                            <input type="text" id="email" name="email" class="form-control" style="height: 3rem" placeholder="Enter your email/phone number" autofocus required/>
                            {{-- <label class="form-label" for="form3Example3">Email address</label> --}}
                        </div>

                        <!-- Password input -->
                        <div data-mdb-input-init class="form-outline mb-4">
                            <input type="password" id="password" name="password" class="form-control" style="height: 3rem" placeholder="Enter token" required/>
                            {{-- <label class="form-label" for="form3Example4">Password</label> --}}
                        </div>

                        <div class="text-center text-lg-start mt-4 pt-2">
                            <button  type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-block w-100 rounded-pill mb-4" style="padding-left: 2.5rem; padding-right: 2.5rem; font-weight: bolder; height: 3rem; font-size: 1.2rem">
                                {{ __('Log in') }}
                            </button>

                            <a href="/" class="text-body" style="margin-left: 38%;">{{ __('To Welcome Page') }}</a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
</body>
</html>


