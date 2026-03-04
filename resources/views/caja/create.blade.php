@extends('layouts.app')

@section('title','Crear caja')

@push('css')
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

        <div class="row g-4 justify-content-center">

            <div class="col-12 col-md-4">
                <label for="billetes" class="form-label fw-semibold">💵 Billetes</label>
                <input type="text" id="billetes" class="form-control form-control-lg"
                       inputmode="numeric" value="0" placeholder="0" autocomplete="off">
            </div>

            <div class="col-12 col-md-4">
                <label for="monedas" class="form-label fw-semibold">🪙 Monedas</label>
                <input type="text" id="monedas" class="form-control form-control-lg"
                       inputmode="numeric" value="0" placeholder="0" autocomplete="off">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label fw-semibold">💰 Total</label>
                <div class="form-control form-control-lg fw-bold"
                     id="saldo_total_display"
                     style="background:#f0fdf4; color:#15803d; font-size:1.3rem;">$0</div>
                <input type="hidden" id="saldo_inicial" name="saldo_inicial" value="0" required>
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
    // Convierte "1234567" → "1.234.567"
    function formatNum(n) {
        return n.toLocaleString('es-CO');
    }

    // Parsea el campo (puede estar formateado con puntos)
    function parseNum(str) {
        return parseInt(str.replace(/\./g, '')) || 0;
    }

    function recalcular() {
        const billetes = parseNum(document.getElementById('billetes').value);
        const monedas  = parseNum(document.getElementById('monedas').value);
        document.getElementById('saldo_total_display').textContent = '$' + formatNum(billetes + monedas);
        document.getElementById('saldo_inicial').value = billetes + monedas;
    }

    function setupInput(id) {
        const el = document.getElementById(id);

        // Al hacer foco: si el valor es 0 o "0", vaciar
        el.addEventListener('focus', function() {
            if (!this.value || this.value === '0') this.value = '';
        });

        // Al salir: si está vacío, volver a 0
        el.addEventListener('blur', function() {
            if (!this.value) this.value = '0';
            // Formatear con puntos
            const n = parseNum(this.value);
            this.value = n === 0 ? '0' : formatNum(n);
            recalcular();
        });

        // Al escribir: solo dígitos, formatea al vuelo
        el.addEventListener('input', function() {
            const raw = this.value.replace(/\./g, '').replace(/\D/g, '');
            const n = parseInt(raw) || 0;
            // Mantener cursor al final formateando
            this.value = n === 0 ? '' : formatNum(n);
            recalcular();
        });
    }

    setupInput('billetes');
    setupInput('monedas');
    recalcular();
</script>
@endpush

