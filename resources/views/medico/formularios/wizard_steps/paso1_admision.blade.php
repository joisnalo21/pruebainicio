{{-- Alerts --}}
@if (session('success'))
<div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
    {{ session('success') }}
</div>
@endif

@if ($errors->any())
<div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
    <div class="font-semibold mb-2">Revisa estos campos:</div>
    <ul class="list-disc ml-5 text-sm space-y-1">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('medico.formularios.paso.store', ['formulario' => $formulario->id, 'paso' => 1]) }}" class="space-y-6">
    @csrf

    {{-- Card: Encabezado --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <div class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Encabezado</h3>
                <p class="text-sm text-gray-500">Datos fijos del hospital (solo lectura).</p>
            </div>
            <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700">
                Fijo
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="institucion_sistema" class="block text-sm font-medium text-gray-700 mb-1">Institución del sistema</label>
                <input id="institucion_sistema" type="text" class="w-full rounded-xl border-gray-300 bg-gray-50"
                    value="{{ $formulario->institucion_sistema ?? '—' }}" readonly>
            </div>

            <div>
                <label for="unidad_operativa" class="block text-sm font-medium text-gray-700 mb-1">Unidad operativa</label>
                <input id="unidad_operativa" type="text" class="w-full rounded-xl border-gray-300 bg-gray-50"
                    value="{{ $formulario->unidad_operativa ?? '—' }}" readonly>
            </div>

            <div>
                <label for="cod_uo" class="block text-sm font-medium text-gray-700 mb-1">Cód. UO</label>
                <input id="cod_uo" type="text" name="cod_uo"
                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                    value="{{ old('cod_uo', $formulario->cod_uo) }}"
                    placeholder="Ej: 13D01">
            </div>

            <div>
                <label for="numero_historia_clinica" class="block text-sm font-medium text-gray-700 mb-1">N° Historia clínica</label>
                <input id="numero_historia_clinica" type="text" name="numero_historia_clinica"
                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                    value="{{ old('numero_historia_clinica', $formulario->numero_historia_clinica) }}"
                    placeholder="Ej: HC-000123">
            </div>
        </div>

    </div>

    {{-- Card: Registro de admisión --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Registro de admisión</h3>
        <p class="text-sm text-gray-500 mb-4">Completa la información inicial de ingreso.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="fecha_admision" class="block text-sm font-medium text-gray-700 mb-1">
                    Fecha y hora de admisión <span class="text-red-500">*</span>
                </label>
                <input id="fecha_admision" type="datetime-local" name="fecha_admision"
                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                    value="{{ old('fecha_admision', $formulario->fecha_admision ? \Carbon\Carbon::parse($formulario->fecha_admision)->format('Y-m-d\TH:i') : '' ) }}">
                <p class="mt-1 text-xs text-gray-500">Ej: hora de llegada a emergencia.</p>
            </div>

            <div>
                <label for="referido_de" class="block text-sm font-medium text-gray-700 mb-1">Referido de</label>
                <input id="referido_de" type="text" name="referido_de"
                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                    value="{{ old('referido_de', $formulario->referido_de) }}"
                    placeholder="Centro de salud, policía, otro hospital...">
            </div>
        </div>
    </div>

    {{-- Card: Llegada --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Llegada y fuente de información</h3>
        <p class="text-sm text-gray-500 mb-4">Selecciona cómo llega y quién entrega/da información.</p>

        @php $v = old('forma_llegada', $formulario->forma_llegada); @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="forma_llegada_ambulatorio" class="block text-sm font-medium text-gray-700 mb-2">
                    Forma de llegada <span class="text-red-500">*</span>
                </label>

                {{-- Radios bonitos --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                    @foreach(['ambulatorio' => 'Ambulatorio', 'ambulancia' => 'Ambulancia', 'otro' => 'Otro'] as $key => $label)
                    <label for="forma_llegada_{{ $key }}" class="cursor-pointer rounded-xl border px-3 py-3 flex items-center gap-2
                            {{ $v === $key ? 'border-gray-900 bg-gray-50' : 'border-gray-200 hover:bg-gray-50' }}">
                        <input id="forma_llegada_{{ $key }}" type="radio" name="forma_llegada" value="{{ $key }}"
                            class="text-gray-900 focus:ring-gray-900"
                            {{ $v === $key ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-800">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>

                <p class="mt-1 text-xs text-gray-500">Solo una opción.</p>
            </div>

            <div>
                <label for="fuente_informacion" class="block text-sm font-medium text-gray-700 mb-1">Fuente de información</label>
                <input id="fuente_informacion" type="text" name="fuente_informacion"
                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                    value="{{ old('fuente_informacion', $formulario->fuente_informacion) }}"
                    placeholder="Paciente, familiar, paramédico...">
            </div>

            <div>
                <label for="entrega_institucion_persona" class="block text-sm font-medium text-gray-700 mb-1">Institución / persona que entrega</label>
                <input id="entrega_institucion_persona" type="text" name="entrega_institucion_persona"
                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                    value="{{ old('entrega_institucion_persona', $formulario->entrega_institucion_persona) }}">
            </div>

            <div>
                <label for="entrega_telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono (entrega)</label>
                <input id="entrega_telefono" type="text" name="entrega_telefono"
                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                    value="{{ old('entrega_telefono', $formulario->entrega_telefono) }}">
            </div>
        </div>
    </div>

    {{-- Card: Contacto para avisar --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <h3 class="text-lg font-semibold text-gray-900 mb-1">En caso necesario avisar a</h3>
        <p class="text-sm text-gray-500 mb-4">Contacto alterno del paciente (opcional).</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="avisar_nombre" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input id="avisar_nombre" type="text" name="avisar_nombre"
                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                    value="{{ old('avisar_nombre', $formulario->avisar_nombre) }}">
            </div>

            <div>
                <label for="avisar_parentesco" class="block text-sm font-medium text-gray-700 mb-1">Parentesco / afinidad</label>
                <input id="avisar_parentesco" type="text" name="avisar_parentesco"
                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                    value="{{ old('avisar_parentesco', $formulario->avisar_parentesco) }}">
            </div>

            <div>
                <label for="avisar_direccion" class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                <input id="avisar_direccion" type="text" name="avisar_direccion"
                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                    value="{{ old('avisar_direccion', $formulario->avisar_direccion) }}">
            </div>

            <div>
                <label for="avisar_telefono" class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                <input id="avisar_telefono" type="text" name="avisar_telefono"
                    class="w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-gray-900"
                    value="{{ old('avisar_telefono', $formulario->avisar_telefono) }}">
            </div>
        </div>
    </div>

    {{-- Footer actions sticky-like --}}
    <div class="flex flex-col sm:flex-row gap-2 justify-end pt-2">
        <button type="submit" name="accion" value="save"
            class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 font-semibold">
            Guardar borrador
        </button>

        <button type="submit" name="accion" value="next"
            class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white font-semibold">
            Guardar y continuar →
        </button>
    </div>
</form>

