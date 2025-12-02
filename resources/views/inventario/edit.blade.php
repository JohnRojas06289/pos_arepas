@extends('layouts.app')

@section('title','Editar inventario')

@push('css')
@endpush

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Editar Inventario</h1>

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
                <x-forms.input id="cantidad" required='true' type='number' :value="$inventario->cantidad" />
            </div>

            <!-----Fecha de vencimiento----->
            <div class="col-md-6">
                <x-forms.input id="fecha_vencimiento" type='date' labelText='Fecha de Vencimiento' :value="$inventario->fecha_vencimiento" />
            </div>

              <!-----Costo Unitario----->
              <div class="col-md-6">
                <x-forms.input id="costo_unitario" type='number' labelText='Costo unitario' required='true' :value="$inventario->producto->precio_compra" />
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
