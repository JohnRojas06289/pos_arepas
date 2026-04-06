@props([
    'href',
    'content',
    'active' => false,
])

<a class="nav-link {{ $active ? 'active' : '' }}" href="{{ $href }}" @if($active) aria-current="page" @endif>
    {{ $content }}
</a>
