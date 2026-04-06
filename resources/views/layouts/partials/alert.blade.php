@php
    $flashMessages = collect([
        ['type' => 'success', 'icon' => 'fa-circle-check', 'message' => session('success')],
        ['type' => 'danger', 'icon' => 'fa-circle-xmark', 'message' => session('error')],
        ['type' => 'info', 'icon' => 'fa-circle-info', 'message' => session('login')],
    ])->filter(fn ($item) => filled($item['message']));
@endphp

@if($flashMessages->isNotEmpty() || $errors->any())
<div class="container-fluid px-3 pt-3 flash-stack" aria-live="polite">
    @foreach($flashMessages as $item)
    <div class="alert alert-{{ $item['type'] }} alert-dismissible fade show shadow-sm d-flex align-items-start gap-2 mb-3" role="alert">
        <i class="fas {{ $item['icon'] }} mt-1"></i>
        <div class="flex-grow-1">
            {{ $item['message'] }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    @endforeach

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="fas fa-circle-exclamation mt-1"></i>
            <div class="flex-grow-1">
                <div class="fw-semibold mb-1">Revisa los datos ingresados.</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
    @endif
</div>
@endif
