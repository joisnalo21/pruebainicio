@extends('layouts.app')

@section('content')
<div class="p-6 text-gray-800">
    <h1 class="text-2xl font-bold text-blue-700 mb-3">Panel de Enfermería</h1>
    <p>Bienvenida/o, {{ Auth::user()->name }}. Aquí puedes consultar fichas y visualizar datos de pacientes.</p>
</div>
@endsection
