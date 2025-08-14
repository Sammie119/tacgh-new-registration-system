@props(['route' => '', 'title' => ''])

<li>
    <a href="{{ route($route) }}" class="{{ (Request::segment(2) == $route) ? 'active' : '' }}">
        <i class="bi bi-circle"></i><span>{{ $title }}</span>
    </a>
</li>


