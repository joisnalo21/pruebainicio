@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto bg-white shadow-md rounded-xl p-6 mt-10">
  <h2 class="text-xl font-semibold text-blue-700 mb-4">Registrar Formulario 008</h2>

  <form method="POST" action="{{ route('medico.formulario.guardar') }}" class="space-y-4">
    @csrf
    <input type="hidden" name="paciente_id" value="{{ $paciente->id }}">

    <div>
      <label class="block text-sm font-medium text-gray-700">Paciente</label>
      <input type="text" value="{{ $paciente->nombre }}" disabled class="w-full border border-gray-300 rounded-lg p-2.5 bg-gray-100">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Motivo de atención</label>
      <textarea name="motivo" rows="2" required class="w-full border border-gray-300 rounded-lg p-2.5"></textarea>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Diagnóstico</label>
      <textarea name="diagnostico" rows="2" class="w-full border border-gray-300 rounded-lg p-2.5"></textarea>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700">Tratamiento</label>
      <textarea name="tratamiento" rows="2" class="w-full border border-gray-300 rounded-lg p-2.5"></textarea>
    </div>

    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg">Guardar Formulario</button>
  </form>
</div>
@endsection
