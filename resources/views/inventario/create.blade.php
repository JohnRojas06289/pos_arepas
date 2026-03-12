@extends('layouts.app')

@section('title', isset($isReinitializing) && $isReinitializing ? 'Reinicializar producto' : 'Inicializar producto')

@push('css')
@endpush

@section('content')
<div class="container-fluid px-2">
    <h1 class="mt-1 text-center">
        {{ isset($isReinitializing) && $isReinitializing ? 'Reinicializar' : 'Inicializar' }} Producto
    </h1>

    <x-breadcrumb.template>
        <x-breadcrumb.item :href="route('panel')" content="Inicio" />
        <x-breadcrumb.item :href="route('productos.index')" content="Productos" />
        <x-breadcrumb.item active='true' content="{{ isset($isReinitializing) && $isReinitializing ? 'Reinicializar' : 'Inicializar' }} producto" />
    </x-breadcrumb.template>

    @if(isset($isReinitializing) && $isReinitializing)
    <div class="alert alert-warning mb-3" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Reinicialización:</strong> Esto actualizará el stock actual del producto y registrará un nuevo movimiento de apertura en el kardex.
        Stock actual: <strong>{{ $inventario->cantidad ?? 0 }}</strong> unidades.
    </div>
    @endif

    <x-forms.template :action="route('inventario.store')" method='post'>

        <x-slot name='header'>
            <p>Producto: <span class='fw-bold'>{{$producto->nombre_completo}}</span></p>
        </x-slot>

        <div class="row g-4">

            <!-----Producto id---->
            <input type="hidden" name="producto_id" value="{{$producto->id}}">

            <!---Cantidad--->
            <div class="col-md-6">
                <x-forms.input
                    id="cantidad"
                    required='true'
                    type='number'
                    :value="isset($inventario) ? $inventario->cantidad : old('cantidad')"
                />
            </div>

            <!-----Fecha de vencimiento----->
            <div class="col-md-6">
                <x-forms.input
                    id="fecha_vencimiento"
                    type='date'
                    labelText='Fecha de Vencimiento'
                    :value="isset($inventario) && $inventario->fecha_vencimiento ? $inventario->fecha_vencimiento->format('Y-m-d') : old('fecha_vencimiento')"
                />
            </div>

            <!-----Costo Unitario----->
            <div class="col-md-6">
                <x-forms.input
                    id="costo_unitario"
                    type='number'
                    labelText='Costo unitario'
                    required='true'
                    :value="isset($ultimoKardex) ? $ultimoKardex->costo_unitario : old('costo_unitario')"
                />
            </div>

            <!-----Precio de Venta----->
            <div class="col-md-6">
                <x-forms.input
                    id="precio_venta"
                    type='number'
                    labelText='Precio de venta'
                    required='true'
                    :value="$producto->precio ?? old('precio_venta')"
                />
            </div>
        </div>

        <x-slot name='footer'>
            <button type="submit" class="btn btn-primary">
                {{ isset($isReinitializing) && $isReinitializing ? 'Reinicializar' : 'Inicializar' }}
            </button>
        </x-slot>

    </x-forms.template>

</div>
@endsection

@push('js')
@endpush


