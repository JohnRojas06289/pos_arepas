@props([
    'id',
    'icon',
    'content',
    'active' => false,
    'expanded' => false,
])

<a class="nav-link collapsed {{ $active ? 'active' : '' }}" href="#"
    data-bs-toggle="collapse"
    data-bs-target="#{{ $id }}"
    aria-expanded="{{ $expanded ? 'true' : 'false' }}"
    aria-controls="{{ $id }}">
    <div class="sb-nav-link-icon"><i class="{{ $icon }}"></i></div>
    <span class="nav-link-text">{{ $content }}</span>
    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
</a>
<div class="collapse {{ $expanded ? 'show' : '' }}" id="{{ $id }}"
    data-bs-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">
        {{ $slot }}
    </nav>
</div>
