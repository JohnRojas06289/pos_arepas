@extends('layouts.app')

@section('title','Crear Producto')

@push('css')
<style>
    #descripcion {
        resize: none;
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
@endpush

@section('content')
<div class="container-fluid px-2">
    <h1 class="mt-1 text-center">Crear Producto</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item"><a href="{{ route('productos.index')}}">Productos</a></li>
        <li class="breadcrumb-item active">Crear producto</li>
    </ol>

    <div class="card">
        <form action="{{ route('productos.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="card-body text-bg-light">

                <div class="row g-4">

                    <!---Nombre---->
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre:</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" value="{{old('nombre')}}" required>
                        @error('nombre')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!----Codigo---->
                    <div class="col-md-3">
                        <label for="codigo" class="form-label">Código:</label>
                        <input type="text" name="codigo" id="codigo" class="form-control" 
                               value="{{old('codigo')}}" 
                               placeholder="Sugerido: {{ $codigoSugerido }}">
                        @error('codigo')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!---Precio---->
                    <div class="col-md-3">
                        <label for="precio" class="form-label">Precio Venta:</label>
                        <input type="number" name="precio" id="precio" class="form-control" step="0.01" min="0" value="{{old('precio')}}">
                        @error('precio')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!---Descripción---->
                    <div class="col-12">
                        <label for="descripcion" class="form-label">Descripción:</label>
                        <textarea name="descripcion" id="descripcion" rows="2" class="form-control">{{old('descripcion')}}</textarea>
                        @error('descripcion')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                </div>

                <hr>

                <div class="row g-4">
                    <!---Talla (Antes Presentación)---->
                    <div class="col-md-3">
                        <label for="presentacione_id" class="form-label">Talla (Opcional):</label>
                        <select data-size="4"
                            title="Seleccione una talla"
                            data-live-search="true"
                            name="presentacione_id"
                            id="presentacione_id"
                            class="form-control selectpicker show-tick">
                            <option value="">Ninguna</option>
                            @foreach ($presentaciones as $item)
                            <option value="{{$item->id}}" {{ old('presentacione_id') == $item->id ? 'selected' : '' }}>
                                {{$item->nombre}}
                            </option>
                            @endforeach
                        </select>
                        @error('presentacione_id')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!---Color---->
                    <div class="col-md-3">
                        <label for="color" class="form-label">Color:</label>
                        <input type="text" name="color" id="color" class="form-control" value="{{old('color')}}">
                        @error('color')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!---Material---->
                    <div class="col-md-3">
                        <label for="material" class="form-label">Material:</label>
                        <input type="text" name="material" id="material" class="form-control" value="{{old('material')}}">
                        @error('material')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!---Género---->
                    <div class="col-md-3">
                        <label for="genero" class="form-label">Género:</label>
                        <select name="genero" id="genero" class="form-control selectpicker show-tick">
                            <option value="Unisex" {{ old('genero') == 'Unisex' ? 'selected' : '' }}>Unisex</option>
                            <option value="Hombre" {{ old('genero') == 'Hombre' ? 'selected' : '' }}>Hombre</option>
                            <option value="Mujer" {{ old('genero') == 'Mujer' ? 'selected' : '' }}>Mujer</option>
                        </select>
                        @error('genero')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>
                </div>

                <br>

                <div class="row g-4">
                     <!---Marca---->
                     <div class="col-md-4">
                        <label for="marca_id" class="form-label">Marca (Opcional):</label>
                        <select data-size="4"
                            title="Seleccione una marca"
                            data-live-search="true"
                            name="marca_id"
                            id="marca_id"
                            class="form-control selectpicker show-tick">
                            <option value="">Ninguna</option>
                            @foreach ($marcas as $item)
                            <option value="{{$item->id}}" {{ old('marca_id') == $item->id ? 'selected' : '' }}>{{$item->nombre}}</option>
                            @endforeach
                        </select>
                        @error('marca_id')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!---Categorías---->
                    <div class="col-md-4">
                        <label for="categoria_id" class="form-label">Categoría (Opcional):</label>
                        <select data-size="4"
                            title="Seleccione la categoría"
                            data-live-search="true"
                            name="categoria_id"
                            id="categoria_id"
                            class="form-control selectpicker show-tick">
                            <option value="">Ninguna</option>
                            @foreach ($categorias as $item)
                            <option value="{{$item->id}}" {{ old('categoria_id') == $item->id ? 'selected' : '' }}>
                                {{$item->nombre}}
                            </option>
                            @endforeach
                        </select>
                        @error('categoria_id')
                        <small class="text-danger">{{'*'.$message}}</small>
                        @enderror
                    </div>

                    <!---Imagen---->
                    <div class="col-md-4">
                         <label for="img_path" class="form-label">Imagen:</label>
                         <input type="file" name="img_path" id="img_path" class="form-control" accept="image/*">
                         @error('img_path')
                         <small class="text-danger">{{'*'.$message}}</small>
                         @enderror
                     </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12 text-center">
                         <img id="img-default"
                            class="img-fluid"
                            style="max-height: 200px;"
                            src="{{ asset('assets/img/paisaje.png') }}"
                            alt="Imagen por defecto">

                        <img src="" alt="Vista previa"
                            id="img-preview"
                            class="img-fluid img-thumbnail" 
                            style="display: none; max-height: 200px;">
                    </div>
                </div>

            </div>

            <div class="card-footer text-center">
                <button type="submit" class="btn btn-primary">Guardar Producto</button>
            </div>
        </form>
    </div>


</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/browser-image-compression@2.0.2/dist/browser-image-compression.js"></script>
<script>
    const inputImagen = document.getElementById('img_path');
    const imagenPreview = document.getElementById('img-preview');
    const imagenDefault = document.getElementById('img-default');
    const submitBtn = document.querySelector('button[type="submit"]');

    if (inputImagen) {
        inputImagen.addEventListener('change', async function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // Si la imagen es mayor a 1MB, comprimir
                if (file.size > 1024 * 1024) {
                    // Deshabilitar botón para evitar envío antes de tiempo
                    if(submitBtn) {
                        submitBtn.disabled = true;
                        const originalText = submitBtn.innerText;
                        submitBtn.innerText = '⏳ Comprimiendo imagen...';
                    }

                    try {
                        const options = {
                            maxSizeMB: 0.5, // Objetivo: 500KB
                            maxWidthOrHeight: 1280,
                            useWebWorker: true
                        };
                        
                        const compressedFile = await imageCompression(file, options);
                        
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(new File([compressedFile], file.name, { type: file.type }));
                        inputImagen.files = dataTransfer.files;
                        
                        console.log(`Imagen comprimida: ${(compressedFile.size / 1024 / 1024).toFixed(2)} MB`);
                    } catch (error) {
                        console.error('Error al comprimir:', error);
                        alert('Error al procesar la imagen. Intenta con una más pequeña.');
                    } finally {
                        // Habilitar botón de nuevo
                        if(submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerText = 'Guardar Producto'; // Reset text to prevent issues if originalText is lost scope
                        }
                    }
                }

                // Mostrar preview
                if(imagenPreview && imagenDefault) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imagenPreview.src = e.target.result;
                        imagenPreview.style.display = 'block';
                        imagenDefault.style.display = 'none';
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            }
        });
    }
</script>
@endpush


