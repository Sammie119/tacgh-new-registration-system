
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>@yield('title', 'TAC-GH Registration')</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="{{  asset("assets/img/favicon.png") }}" rel="icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{  asset("assets/vendor/bootstrap/css/bootstrap.min.css") }}" rel="stylesheet">
    <link href="{{  asset("assets/vendor/bootstrap-icons/bootstrap-icons.css") }}" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{  asset("assets/css/style.css") }}" rel="stylesheet">

</head>

<body>
    @if(!empty(Session::get('registrant')))
        @include("layouts.navbar_2")
    @endisset

    @yield('content')

<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

<script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>

<!-- Vendor JS Files -->
<script src="{{  asset("assets/vendor/bootstrap/js/bootstrap.bundle.js") }}"></script>

<!-- Template Main JS File -->
<script src="{{  asset("assets/js/main.js") }}"></script>

</body>

</html>
