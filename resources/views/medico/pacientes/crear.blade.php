@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto bg-white shadow-md rounded-xl p-6 mt-10">
  <h2 class="text-xl font-semibold text-blue-700 mb-4">Registrar nuevo paciente</h2>

  <form method="POST" action="{{ route('medico.paciente.guardar') }}" class="space-y-4">
    @csrf
    <div>
      <label class="block text-sm font-medium text-gray-700">Cédula</label>
      <input name="cedula" type="text" required class="w-full border border-gray-300 rounded-lg p-2.5">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Nombre completo</label>
      <input name="nombre" type="text" required class="w-full border border-gray-300 rounded-lg p-2.5">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Edad</label>
      <input name="edad" type="number" required class="w-full border border-gray-300 rounded-lg p-2.5">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Dirección</label>
      <input name="direccion" type="text" class="w-full border border-gray-300 rounded-lg p-2.5">
    </div>

    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-5 rounded-lg">Guardar</button>
  </form>
</div>
@endsection
