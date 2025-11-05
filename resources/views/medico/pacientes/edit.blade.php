@extends('layouts.medico')

@section('title', 'Editar Paciente')

@section('content')
<div class="min-h-screen flex justify-center items-start py-10 bg-gray-50">
    <div class="bg-white shadow-xl rounded-2xl w-full max-w-2xl border border-gray-200 p-10">
        
        {{-- Encabezado --}}
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-800">Editar paciente</h1>
            <p class="text-gray-500 mt-1">Actualice los datos del paciente</p>
        </div>

        {{-- Mostrar errores --}}
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg shadow-sm">
                <ul class="list-disc pl-5 text-left">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulario --}}
        <form method="POST" action="{{ route('medico.pacientes.update', $paciente->id) }}" class="space-y-6 text-gray-800">
            @csrf
            @method('PUT')

            {{-- Cédula --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Cédula *</label>
                <input type="text" name="cedula" id="cedula"
                       value="{{ old('cedula', $paciente->cedula) }}" required
                       maxlength="10"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition">
                <p id="mensajeCedula" class="text-sm mt-1"></p>
            </div>

            {{-- Nombres --}}
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Primer nombre *</label>
                    <input type="text" name="primer_nombre"
                           value="{{ old('primer_nombre', $paciente->primer_nombre) }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Segundo nombre *</label>
                    <input type="text" name="segundo_nombre"
                           value="{{ old('segundo_nombre', $paciente->segundo_nombre) }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
            </div>

            {{-- Apellidos --}}
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Apellido paterno *</label>
                    <input type="text" name="apellido_paterno"
                           value="{{ old('apellido_paterno', $paciente->apellido_paterno) }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Apellido materno *</label>
                    <input type="text" name="apellido_materno"
                           value="{{ old('apellido_materno', $paciente->apellido_materno) }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
            </div>

            {{-- Fecha de nacimiento y edad --}}
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha de nacimiento *</label>
                    <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                           value="{{ old('fecha_nacimiento', $paciente->fecha_nacimiento) }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Edad</label>
                    <input type="number" name="edad" id="edad"
                           value="{{ old('edad', $paciente->edad) }}"
                           class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-2">
                </div>
            </div>

            {{-- Dirección --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Dirección *</label>
                <input type="text" name="direccion"
                       value="{{ old('direccion', $paciente->direccion) }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>

            {{-- Sexo --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Sexo *</label>
                <select name="sexo" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    <option value="">Seleccione</option>
                    <option value="Masculino" {{ old('sexo', $paciente->sexo) == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                    <option value="Femenino" {{ old('sexo', $paciente->sexo) == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                    <option value="Otro" {{ old('sexo', $paciente->sexo) == 'Otro' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>

            {{-- Provincia, Cantón, Parroquia --}}
            <div class="grid grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Provincia *</label>
                    <select id="provincia" name="provincia" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">Seleccione provincia</option>
                        @foreach ($provincias as $codigo => $prov)
                            <option value="{{ $codigo }}" {{ old('provincia', $paciente->provincia) == $codigo ? 'selected' : '' }}>
                                {{ $prov }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Cantón *</label>
                    <select id="canton" name="canton" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">Seleccione cantón</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Parroquia *</label>
                    <select id="parroquia" name="parroquia" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">Seleccione parroquia</option>
                    </select>
                </div>
            </div>

            {{-- Teléfono y ocupación --}}
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Teléfono *</label>
                    <input type="text" name="telefono" maxlength="10"
                           value="{{ old('telefono', $paciente->telefono) }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Ocupación *</label>
                    <input type="text" name="ocupacion"
                           value="{{ old('ocupacion', $paciente->ocupacion) }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>
            </div>

            {{-- Botones --}}
            <div class="flex justify-end space-x-4 pt-8 border-t mt-8">
                <a href="{{ route('medico.pacientes.index') }}"
                   class="border border-gray-400 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-100 transition">
                    Cancelar
                </a>
                <button type="submit"
                        class="bg-blue-600 text-white px-8 py-2 rounded-lg hover:bg-blue-700 transition">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let provinciasJSON = {};
    const oldProvincia = "{{ old('provincia', $paciente->provincia) }}";
    const oldCanton = "{{ old('canton', $paciente->canton) }}";
    const oldParroquia = "{{ old('parroquia', $paciente->parroquia) }}";

    fetch('{{ asset('provincias.json') }}')
        .then(res => res.json())
        .then(data => {
            provinciasJSON = data;
            if (oldProvincia) {
                cargarCantones(oldProvincia);
                if (oldCanton) cargarParroquias(oldProvincia, oldCanton);
            }
        });

    const provinciaSelect = document.getElementById('provincia');
    const cantonSelect = document.getElementById('canton');
    const parroquiaSelect = document.getElementById('parroquia');

    provinciaSelect.addEventListener('change', function () {
        cargarCantones(this.value);
    });

    cantonSelect.addEventListener('change', function () {
        cargarParroquias(provinciaSelect.value, this.value);
    });

    function cargarCantones(codigoProvincia) {
        cantonSelect.innerHTML = '<option value="">Seleccione cantón</option>';
        parroquiaSelect.innerHTML = '<option value="">Seleccione parroquia</option>';
        if (codigoProvincia && provinciasJSON[codigoProvincia]) {
            const cantones = provinciasJSON[codigoProvincia]['cantones'];
            for (const codigo in cantones) {
                cantonSelect.innerHTML += `<option value="${codigo}" ${codigo === oldCanton ? 'selected' : ''}>
                    ${cantones[codigo]['canton']}
                </option>`;
            }
        }
    }

    function cargarParroquias(codigoProvincia, codigoCanton) {
        parroquiaSelect.innerHTML = '<option value="">Seleccione parroquia</option>';
        if (codigoProvincia && codigoCanton &&
            provinciasJSON[codigoProvincia]['cantones'][codigoCanton]) {
            const parroquias = provinciasJSON[codigoProvincia]['cantones'][codigoCanton]['parroquias'];
            for (const codigo in parroquias) {
                parroquiaSelect.innerHTML += `<option value="${parroquias[codigo]}" ${parroquias[codigo] === oldParroquia ? 'selected' : ''}>
                    ${parroquias[codigo]}
                </option>`;
            }
        }
    }

    // === CALCULAR EDAD AUTOMÁTICAMENTE ===
    const fechaNacimiento = document.getElementById('fecha_nacimiento');
    const edadInput = document.getElementById('edad');

    fechaNacimiento.addEventListener('change', function () {
        if (this.value) {
            const nacimiento = new Date(this.value);
            const hoy = new Date();
            let edad = hoy.getFullYear() - nacimiento.getFullYear();
            const mes = hoy.getMonth() - nacimiento.getMonth();
            if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) edad--;
            edadInput.value = edad >= 0 ? edad : '';
        } else {
            edadInput.value = '';
        }
    });
});
</script>
@endpush
