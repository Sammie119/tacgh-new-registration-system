@props(['page' => 'Home'])

<div class="pagetitle">
    <h1>{{ $page }}</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">{{ $page }}</li>
        </ol>
    </nav>
</div><!-- End Page Title -->
