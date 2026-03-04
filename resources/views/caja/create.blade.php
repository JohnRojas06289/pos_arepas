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
                <input type="number" id="billetes" class="form-control form-control-lg"
                       min="0" value="0" placeholder="0">
            </div>

            <div class="col-12 col-md-4">
                <label for="monedas" class="form-label fw-semibold">🪙 Monedas</label>
                <input type="number" id="monedas" class="form-control form-control-lg"
                       min="0" value="0" placeholder="0">
            </div>

            <div class="col-12 col-md-4">
                <label for="saldo_inicial" class="form-label fw-semibold">💰 Total</label>
                <input type="number" id="saldo_inicial" name="saldo_inicial"
                       class="form-control form-control-lg fw-bold"
                       style="background:#f0fdf4; color:#15803d;"
                       required value="0" readonly>
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
    function recalcular() {
        const billetes = parseInt(document.getElementById('billetes').value) || 0;
        const monedas  = parseInt(document.getElementById('monedas').value)  || 0;
        document.getElementById('saldo_inicial').value = billetes + monedas;
    }

    document.getElementById('billetes').addEventListener('input', recalcular);
    document.getElementById('monedas').addEventListener('input', recalcular);
</script>
@endpush
