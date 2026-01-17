@extends('layouts.app')

@section('title','Editar inventario')

@push('css')
@endpush

@section('content')
<div class="container-fluid px-2">
    <h1 class="mt-1 text-center">Editar Inventario</h1>

    <x-breadcrumb.template>
        <x-breadcrumb.item :href="route('panel')" content="Inicio" />
        <x-breadcrumb.item :href="route('inventario.index')" content="Inventario" />
        <x-breadcrumb.item active='true' content="Editar inventario" />
    </x-breadcrumb.template>

    <x-forms.template :action="route('inventario.update', $inventario->id)" method='post'>
        @method('PUT')
        <x-slot name='header'>
            <p>Producto: <span class='fw-bold'>{{$producto->nombre_completo}}</span></p>
        </x-slot>

        <div class="row g-4">

            <!-----Producto id---->
            <input type="hidden" name="producto_id" value="{{$producto->id}}">

            <!---Cantidad--->
            <div class="col-md-6">
                <x-forms.input id="cantidad" required='true' type='number' :defaultValue="$inventario->cantidad" />
            </div>

            <!-----Fecha de vencimiento----->
            <div class="col-md-6">
                <x-forms.input id="fecha_vencimiento" type='date' labelText='Fecha de Vencimiento' :defaultValue="$inventario->fecha_vencimiento ? $inventario->fecha_vencimiento->format('Y-m-d') : ''" />
            </div>

              <!-----Costo Unitario----->
              <div class="col-md-6">
                <x-forms.input id="costo_unitario" type='number' step="0.01" labelText='Costo unitario' required='true' :defaultValue="$costo_unitario" />
            </div>

            <!-----Precio Venta----->
            <div class="col-md-6">
                <x-forms.input id="precio_venta" type='number' step="0.01" labelText='Precio de Venta' required='true' :defaultValue="$producto->precio" />
            </div>
        </div>

        <x-slot name='footer'>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </x-slot>

    </x-forms.template>

</div>
@endsection

@push('js')

@endpush


