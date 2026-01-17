@extends('layouts.app')

@section('title','Empresa')

@push('css')
<script src="{{ asset('js/sweetalert2.min.js') }}"></script>
@endpush

@section('content')
<div class="container-fluid px-2">
    <h1 class="mt-1 text-center">Mi empresa</h1>

    <x-breadcrumb.template>
        <x-breadcrumb.item :href="route('panel')" content="Inicio" />
        <x-breadcrumb.item active='true' content="Mi empresa" />
    </x-breadcrumb.template>

    <x-forms.template :action="route('empresa.update',['empresa' => $empresa])" method='post' patch='true'>

        <div class="row g-4">

            <div class="col-md-6">
                <x-forms.input id="nombre" required='true' :defaultValue='$empresa->nombre' />
            </div>

            <div class="col-md-6">
                <x-forms.input id="propietario" required='true' :defaultValue='$empresa->propietario' />
            </div>

            <div class="col-md-6">
                <x-forms.input id="nit" required='true' :defaultValue='$empresa->nit' />
            </div>

            <div class="col-md-6">
                <x-forms.input id="direccion" required='true' :defaultValue='$empresa->direccion' />
            </div>



            <div class="col-md-4">
                <x-forms.input id="correo" :defaultValue='$empresa->correo' type='email' />
            </div>

            <div class="col-md-4">
                <x-forms.input id="telefono" :defaultValue='$empresa->telefono' />
            </div>

            <div class="col-md-4">
                <x-forms.input id="ubicacion" :defaultValue='$empresa->ubicacion' />
            </div>

            <div class="col-12">

                <label for="moneda_display" class="form-label">Moneda seleccionada:</label>
                <input type="text" id="moneda_display" class="form-control" value="COP - Peso Colombiano" readonly>
                <input type="hidden" name="moneda_id" value="{{$moneda->id}}">
                @error('moneda_id')
                <small class="text-danger">{{$message}}</small>
                @enderror
            </div>

        </div>

        @can('update-empresa')
        <x-slot name='footer'>
            <button type="submit" class="btn btn-primary">Actualizar</button>
        </x-slot>
        @endcan

    </x-forms.template>


</div>
@endsection

@push('js')
@endpush


