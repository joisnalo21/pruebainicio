@extends($layout ?? 'layouts.medico')


@section('title', 'Editar Paciente')

@section('content')
<div class="min-h-screen flex justify-center items-start py-10 bg-gray-50">
    <div class="bg-white shadow-xl rounded-2xl w-full max-w-2xl border border-gray-200 p-10">

        {{-- Encabezado --}}
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-800">Editar paciente</h1>
            <p class="text-gray-500 mt-1">Actualice los datos del paciente</p>
        </div>

        {{-- Errores --}}
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg shadow-sm">
                <ul class="list-disc pl-5 text-left">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route($rp.'pacientes.update', $paciente->id) }}" class="space-y-6 text-gray-800">
            @csrf
            @method('PUT')

            {{-- Cédula --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Cédula *</label>
                <input type="text" name="cedula" maxlength="10"
                       value="{{ old('cedula', $paciente->cedula) }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>

            {{-- Nombres --}}
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold mb-1">Primer nombre *</label>
                    <input type="text" name="primer_nombre" required
                           value="{{ old('primer_nombre', $paciente->primer_nombre) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Segundo nombre *</label>
                    <input type="text" name="segundo_nombre" required
                           value="{{ old('segundo_nombre', $paciente->segundo_nombre) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
            </div>

            {{-- Apellidos --}}
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold mb-1">Apellido paterno *</label>
                    <input type="text" name="apellido_paterno" required
                           value="{{ old('apellido_paterno', $paciente->apellido_paterno) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Apellido materno *</label>
                    <input type="text" name="apellido_materno" required
                           value="{{ old('apellido_materno', $paciente->apellido_materno) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
            </div>

            {{-- Lugar de nacimiento / Nacionalidad --}}
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold mb-1">Lugar de nacimiento *</label>
                    <input type="text" name="lugar_nacimiento" required
                           value="{{ old('lugar_nacimiento', $paciente->lugar_nacimiento) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Nacionalidad *</label>
                    <select id="nacionalidad" name="nacionalidad" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        <option value="">Cargando países...</option>
                    </select>
                </div>
            </div>

            {{-- Fecha nacimiento / edad --}}
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold mb-1">Fecha de nacimiento *</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento"
                           value="{{ old('fecha_nacimiento', $paciente->fecha_nacimiento) }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Edad</label>
                    <input type="number" id="edad" name="edad" readonly
                           value="{{ old('edad', $paciente->edad) }}"
                           class="w-full bg-gray-100 border border-gray-300 rounded-lg px-4 py-2">
                </div>
            </div>

            {{-- Sexo --}}
            <div>
                <label class="block text-sm font-semibold mb-1">Sexo *</label>
                <select name="sexo" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <option value="">Seleccione</option>
                    <option value="Masculino" {{ old('sexo', $paciente->sexo) == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                    <option value="Femenino" {{ old('sexo', $paciente->sexo) == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                    <option value="Otro" {{ old('sexo', $paciente->sexo) == 'Otro' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>

            {{-- Grupo cultural / Estado civil --}}
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold mb-1">Grupo cultural</label>
                    <input type="text" name="grupo_cultural"
                           value="{{ old('grupo_cultural', $paciente->grupo_cultural) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Estado civil</label>
                    <select name="estado_civil"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        <option value="">Seleccione</option>
                        <option value="Soltero/a" {{ old('estado_civil', $paciente->estado_civil) == 'Soltero/a' ? 'selected' : '' }}>Soltero/a</option>
                        <option value="Casado/a" {{ old('estado_civil', $paciente->estado_civil) == 'Casado/a' ? 'selected' : '' }}>Casado/a</option>
                        <option value="Divorciado/a" {{ old('estado_civil', $paciente->estado_civil) == 'Divorciado/a' ? 'selected' : '' }}>Divorciado/a</option>
                        <option value="Viudo/a" {{ old('estado_civil', $paciente->estado_civil) == 'Viudo/a' ? 'selected' : '' }}>Viudo/a</option>
                        <option value="Unión libre" {{ old('estado_civil', $paciente->estado_civil) == 'Unión libre' ? 'selected' : '' }}>Unión libre</option>
                    </select>
                </div>
            </div>

            {{-- Instrucción / Ocupación --}}
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold mb-1">Instrucción</label>
                    <input type="text" name="instruccion"
                           value="{{ old('instruccion', $paciente->instruccion) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Ocupación *</label>
                    <input type="text" name="ocupacion" required
                           value="{{ old('ocupacion', $paciente->ocupacion) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
            </div>

            {{-- Empresa / Seguro --}}
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold mb-1">Empresa donde trabaja</label>
                    <input type="text" name="empresa"
                           value="{{ old('empresa', $paciente->empresa) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Tipo de seguro de salud</label>
                    <input type="text" name="seguro_salud"
                           value="{{ old('seguro_salud', $paciente->seguro_salud) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2">
                </div>
            </div>

            {{-- Provincia / Cantón / Parroquia --}}
            <div class="grid grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-semibold mb-1">Provincia *</label>
                    <select id="provincia" name="provincia" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        <option value="">Seleccione provincia</option>
                        @foreach ($provincias as $codigo => $prov)
                            <option value="{{ $codigo }}"
                                {{ old('provincia', $paciente->provincia) == $codigo ? 'selected' : '' }}>
                                {{ $prov }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Cantón *</label>
                    <select id="canton" name="canton" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        <option value="">Seleccione cantón</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Parroquia *</label>
                    <select id="parroquia" name="parroquia" required
                            class="w-full border border-gray-300 rounded-lg px-4 py-2">
                        <option value="">Seleccione parroquia</option>
                    </select>
                </div>
            </div>

            {{-- Zona --}}
            <div>
                <label class="block text-sm font-semibold mb-1">Zona *</label>
                <select name="zona" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2">
                    <option value="">Seleccione zona</option>
                    <option value="Urbana" {{ old('zona', $paciente->zona) == 'Urbana' ? 'selected' : '' }}>Urbana</option>
                    <option value="Rural" {{ old('zona', $paciente->zona) == 'Rural' ? 'selected' : '' }}>Rural</option>
                </select>
            </div>

            {{-- Barrio --}}
            <div>
                <label class="block text-sm font-semibold mb-1">Barrio *</label>
                <input type="text" name="barrio" required
                       value="{{ old('barrio', $paciente->barrio) }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>

            {{-- Dirección --}}
            <div>
                <label class="block text-sm font-semibold mb-1">Dirección *</label>
                <input type="text" name="direccion" required
                       value="{{ old('direccion', $paciente->direccion) }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>

            {{-- Teléfono --}}
            <div>
                <label class="block text-sm font-semibold mb-1">Teléfono *</label>
                <input type="text" name="telefono" maxlength="10" required
                       value="{{ old('telefono', $paciente->telefono) }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2">
            </div>

            {{-- Botones --}}
            <div class="flex justify-end space-x-4 pt-8 border-t mt-8">
                <a href="{{ route($rp.'pacientes.index') }}"
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

    // ======= PROVINCIAS =======
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

    const provincia = document.getElementById('provincia');
    const canton = document.getElementById('canton');
    const parroquia = document.getElementById('parroquia');

    provincia.addEventListener('change', () => cargarCantones(provincia.value));
    canton.addEventListener('change', () => cargarParroquias(provincia.value, canton.value));

    function cargarCantones(cod) {
        canton.innerHTML = '<option value="">Seleccione cantón</option>';
        parroquia.innerHTML = '<option value="">Seleccione parroquia</option>';

        if (provinciasJSON[cod]) {
            const cantones = provinciasJSON[cod]['cantones'];
            for (const c in cantones) {
                canton.innerHTML += `<option value="${c}" ${c == oldCanton ? 'selected' : ''}>${cantones[c]['canton']}</option>`;
            }
        }
    }

    function cargarParroquias(codProv, codCant) {
        parroquia.innerHTML = '<option value="">Seleccione parroquia</option>';

        if (provinciasJSON[codProv]?.cantones[codCant]) {
            const parroquiasObj = provinciasJSON[codProv].cantones[codCant].parroquias;
            for (const p in parroquiasObj) {
                const val = parroquiasObj[p];
                parroquia.innerHTML += `<option value="${val}" ${val == oldParroquia ? 'selected' : ''}>${val}</option>`;
            }
        }
    }

    // ======= CARGA DE PAÍSES =======
    fetch('{{ asset('countries.json') }}')
        .then(res => res.json())
        .then(lista => {
            const select = document.getElementById('nacionalidad');
            select.innerHTML = "";

            const actual = "{{ old('nacionalidad', $paciente->nacionalidad ?? 'Ecuador') }}";

            lista.forEach(p => {
                const option = document.createElement('option');
                option.value = p.name;
                option.textContent = p.name;
                if (p.name === actual) option.selected = true;
                select.appendChild(option);
            });
        })
        .catch(() => {
            document.getElementById('nacionalidad').innerHTML = '<option>Error cargando países</option>';
        });

    // ======= CALCULAR EDAD =======
    const fechaN = document.getElementById('fecha_nacimiento');
    const edad = document.getElementById('edad');

    fechaN.addEventListener('change', function () {
        if (!this.value) return edad.value = "";

        const nac = new Date(this.value);
        const hoy = new Date();
        let e = hoy.getFullYear() - nac.getFullYear();

        const mes = hoy.getMonth() - nac.getMonth();
        if (mes < 0 || (mes === 0 && hoy.getDate() < nac.getDate())) e--;

        edad.value = e >= 0 ? e : "";
    });

});
</script>
@endpush
