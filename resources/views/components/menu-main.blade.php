@php use Illuminate\Support\Facades\Request; @endphp

@props(['routes_array' => [], 'icon' => 'bi bi-gear-fill', 'title' => '', 'type' => 'single', 'route' => 'dashboard', 'id' => 'sys-admin-nav'])

@if($type === 'single')
    <li class="nav-item">
        <a class="nav-link {{ (Request::segment(2) == $route) ? '' : 'collapsed' }}" href="{{ route($route) }}">
            <i class="{{ $icon }}"></i>
            <span>{{ $title }}</span>
        </a>
    </li>
@else
    <li class="nav-item">
        <a class="nav-link
            <?php
               echo in_array(Request::segment(2), $routes_array) ? '' : ' collapsed';
            ?>
        " data-bs-target="#{{ $id }}" data-bs-toggle="collapse" href="#">
            <i class="{{ $icon }}"></i><span>{{ $title }}</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="{{ $id }}" class="nav-content collapse
            <?php
                foreach ($routes_array as $route){
                    echo (Request::segment(2) == $route) ? ' show' : '';
                }
            ?>
        " data-bs-parent="#sidebar-nav">
            {{ $slot }}
        </ul>
    </li>
@endif




