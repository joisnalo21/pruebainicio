@extends('layouts.app')

@section('content')
<div class="p-6 text-gray-800">
    <h1 class="text-2xl font-bold text-blue-700 mb-3">Panel del Médico</h1>
    <p>Bienvenido, {{ Auth::user()->name }}. Aquí puedes registrar pacientes y llenar el Formulario 008.</p>
</div>
@endsection
