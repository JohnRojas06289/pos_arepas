@props([
    'href',
    'icon',
    'content',
    'active' => false,
])

<a class="nav-link {{ $active ? 'active' : '' }}" href="{{ $href }}" @if($active) aria-current="page" @endif>
    <div class="sb-nav-link-icon"><i class="{{ $icon }}"></i></div>
    <span class="nav-link-text">{{ $content }}</span>
</a>
