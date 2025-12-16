@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Crear Nuevo Formulario</h2>
    <form action="{{ route('formularios.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="motivo">Motivo</label>
            <input type="text" class="form-control" id="motivo" name="motivo" required>
        </div>
        <div class="form-group">
            <label for="diagnostico">Diagnóstico</label>
            <input type="text" class="form-control" id="diagnostico" name="diagnostico" required>
        </div>
        <!-- Agregar más campos según lo necesario -->
        <button type="submit" class="btn btn-success mt-3">Guardar Formulario</button>
    </form>
</div>
@endsection
