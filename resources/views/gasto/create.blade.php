@extends('layouts.app')

@section('title', 'Registrar Gasto')

@section('content')
<div class="container-fluid px-2">
    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
        <div>
            <h1>Registrar Gasto</h1>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('gastos.index') }}">Gastos</a></li>
                <li class="breadcrumb-item active">Nuevo</li>
            </ol>
        </div>
        <a href="{{ route('gastos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header" style="background: var(--color-primary); color: white;">
                    <h5 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Datos del Gasto</h5>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('gastos.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="categoria" class="form-label">Categoría <span class="text-danger">*</span></label>
                            <select name="categoria" id="categoria" class="form-select @error('categoria') is-invalid @enderror" required>
                                <option value="">Seleccionar...</option>
                                @foreach ($categorias as $cat)
                                <option value="{{ $cat->value }}" {{ old('categoria') == $cat->value ? 'selected' : '' }}>
                                    {{ $cat->label() }}
                                </option>
                                @endforeach
                            </select>
                            @error('categoria') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción <span class="text-danger">*</span></label>
                            <input type="text" name="descripcion" id="descripcion"
                                class="form-control @error('descripcion') is-invalid @enderror"
                                value="{{ old('descripcion') }}"
                                placeholder="Ej: Arriendo mes de abril"
                                required>
                            @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="monto" class="form-label">Monto <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" name="monto" id="monto"
                                        class="form-control @error('monto') is-invalid @enderror"
                                        value="{{ old('monto') }}"
                                        min="1" step="1" placeholder="0"
                                        required>
                                </div>
                                @error('monto') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="fecha" class="form-label">Fecha <span class="text-danger">*</span></label>
                                <input type="date" name="fecha" id="fecha"
                                    class="form-control @error('fecha') is-invalid @enderror"
                                    value="{{ old('fecha', now()->toDateString()) }}"
                                    required>
                                @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="metodo_pago" class="form-label">Método de Pago</label>
                            <select name="metodo_pago" id="metodo_pago" class="form-select">
                                <option value="">Sin especificar</option>
                                @foreach ($optionsMetodoPago as $metodo)
                                <option value="{{ $metodo->value }}" {{ old('metodo_pago') == $metodo->value ? 'selected' : '' }}>
                                    {{ $metodo->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="comprobante" class="form-label">Comprobante <small class="text-muted">(PDF, JPG, PNG — máx. 4MB)</small></label>
                            <input type="file" name="comprobante" id="comprobante"
                                class="form-control @error('comprobante') is-invalid @enderror"
                                accept=".pdf,.jpg,.jpeg,.png">
                            @error('comprobante') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="notas" class="form-label">Notas</label>
                            <textarea name="notas" id="notas" rows="3"
                                class="form-control @error('notas') is-invalid @enderror"
                                placeholder="Observaciones adicionales...">{{ old('notas') }}</textarea>
                            @error('notas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Registrar Gasto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
