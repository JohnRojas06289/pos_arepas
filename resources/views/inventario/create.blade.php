@extends('layouts.app')

@section('title','Inicializar producto')

@push('css')
@endpush

@section('content')
<div class="container-fluid px-2">
    <h1 class="mt-1 text-center">Inicializar Producto</h1>

    <x-breadcrumb.template>
        <x-breadcrumb.item :href="route('panel')" content="Inicio" />
        <x-breadcrumb.item :href="route('productos.index')" content="Productos" />
        <x-breadcrumb.item active='true' content="Inicializar producto" />
    </x-breadcrumb.template>



    <x-forms.template :action="route('inventario.store')" method='post'>

        <x-slot name='header'>
            <p>Producto: <span class='fw-bold'>{{$producto->nombre_completo}}</span></p>
        </x-slot>

        <div class="row g-4">

            <!-----Producto id---->
            <input type="hidden" name="producto_id" value="{{$producto->id}}">



            <!---Cantidad--->
            <div class="col-md-6">
                <x-forms.input id="cantidad" required='true' type='number' />
            </div>

            <!-----Fecha de vencimiento----->
            <div class="col-md-6">
                <x-forms.input id="fecha_vencimiento" type='date' labelText='Fecha de Vencimiento' />
            </div>

              <!-----Costo Unitario----->
              <div class="col-md-6">
                <x-forms.input id="costo_unitario" type='number' labelText='Costo unitario' required='true'/>
            </div>

             <!-----Precio de Venta----->
             <div class="col-md-6">
                <x-forms.input id="precio_venta" type='number' labelText='Precio de venta' required='true'/>
            </div>
        </div>

        <x-slot name='footer'>
            <button type="submit" class="btn btn-primary">Inicializar</button>
        </x-slot>

    </x-forms.template>




</div>
@endsection

@push('js')

@endpush


