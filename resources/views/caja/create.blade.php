@extends('layouts.app')

@section('title','Crear caja')

@push('css')
<style>
    .denominacion-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1rem;
        border: 1px solid #e9ecef;
    }
    .denominacion-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.5rem;
    }
    .denominacion-label {
        min-width: 110px;
        font-weight: 600;
        font-size: 0.95rem;
        color: #374151;
    }
    .denominacion-input {
        width: 80px;
        text-align: center;
    }
    .denominacion-subtotal {
        min-width: 110px;
        font-size: 0.9rem;
        color: #6b7280;
        font-weight: 500;
    }
    .total-box {
        background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
        color: white;
        border-radius: 12px;
        padding: 1.25rem 1.5rem;
        font-size: 1.5rem;
        font-weight: 800;
        text-align: center;
        letter-spacing: 0.01em;
        margin-top: 1rem;
    }
    .seccion-titulo {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #6b7280;
        margin-bottom: 0.6rem;
        margin-top: 1rem;
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 0.25rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-2">
    <h1 class="mt-1 text-center">Aperturar Caja</h1>

    <x-breadcrumb.template>
        <x-breadcrumb.item :href="route('panel')" content="Inicio" />
        <x-breadcrumb.item :href="route('cajas.index')" content="Cajas" />
        <x-breadcrumb.item active='true' content="Aperturar caja" />
    </x-breadcrumb.template>

    <x-forms.template :action="route('cajas.store')" method='post'>

        <div class="row g-4">

            {{-- Calculadora de denominaciones --}}
            <div class="col-12 col-lg-8 offset-lg-2">
                <div class="denominacion-card">
                    <h6 class="fw-bold mb-3"><i class="fas fa-money-bill-wave me-2 text-success"></i>Conteo de Billetes y Monedas</h6>

                    <div class="seccion-titulo">💵 Billetes</div>

                    @php
                        $billetes = [100000, 50000, 20000, 10000, 5000, 2000, 1000];
                        $monedas  = [500, 200, 100, 50];
                    @endphp

                    @foreach($billetes as $valor)
                    <div class="denominacion-row">
                        <span class="denominacion-label">${{ number_format($valor, 0, ',', '.') }}</span>
                        <input type="number" class="form-control denominacion-input denominacion-qty"
                               data-valor="{{ $valor }}" min="0" value="0" placeholder="0">
                        <span class="denominacion-subtotal" id="sub_{{ $valor }}">= $0</span>
                    </div>
                    @endforeach

                    <div class="seccion-titulo">🪙 Monedas</div>

                    @foreach($monedas as $valor)
                    <div class="denominacion-row">
                        <span class="denominacion-label">${{ number_format($valor, 0, ',', '.') }}</span>
                        <input type="number" class="form-control denominacion-input denominacion-qty"
                               data-valor="{{ $valor }}" min="0" value="0" placeholder="0">
                        <span class="denominacion-subtotal" id="sub_{{ $valor }}">= $0</span>
                    </div>
                    @endforeach

                    <div class="total-box">
                        Total: <span id="totalGeneral">$0</span>
                    </div>
                </div>
            </div>

            {{-- Campo oculto + visible con el saldo calculado --}}
            <div class="col-12 col-lg-8 offset-lg-2">
                <label for="saldo_inicial" class="form-label fw-semibold">Saldo inicial calculado</label>
                <input type="number" id="saldo_inicial" name="saldo_inicial" class="form-control form-control-lg"
                       required readonly style="background:#f0fdf4; font-size:1.3rem; font-weight:700; color:#15803d;"
                       value="0" placeholder="Se calcula automáticamente">
                <small class="text-muted">También puedes escribir el valor directamente si lo conoces.</small>
                <script>
                    // Allow manual override
                    document.getElementById('saldo_inicial').removeAttribute('readonly');
                </script>
            </div>

        </div>

        <x-slot name='footer'>
            <button type="submit" class="btn btn-primary">Aperturar caja</button>
        </x-slot>

    </x-forms.template>


</div>
@endsection

@push('js')
<script>
    function formatCOP(valor) {
        return '$' + valor.toLocaleString('es-CO');
    }

    function recalcular() {
        let total = 0;
        document.querySelectorAll('.denominacion-qty').forEach(function(input) {
            const valor = parseInt(input.dataset.valor);
            const cantidad = parseInt(input.value) || 0;
            const subtotal = valor * cantidad;
            total += subtotal;
            document.getElementById('sub_' + valor).textContent = '= ' + formatCOP(subtotal);
        });
        document.getElementById('totalGeneral').textContent = formatCOP(total);
        document.getElementById('saldo_inicial').value = total;
    }

    document.querySelectorAll('.denominacion-qty').forEach(function(input) {
        input.addEventListener('input', recalcular);
    });
</script>
@endpush
