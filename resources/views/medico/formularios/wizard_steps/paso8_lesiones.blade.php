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

@php
// Carga inicial desde DB (array) => lo convertimos a JSON para el JS
$lesionesInicial = old('lesiones');
if ($lesionesInicial === null) {
$lesionesInicial = json_encode($formulario->lesiones ?? []);
}
$noAplica = old('no_aplica_lesiones', $formulario->no_aplica_lesiones);
$imgSrc = asset('examenfisico.png'); // en /public
@endphp

<form method="POST" action="{{ route('medico.formularios.paso.store', ['formulario' => $formulario->id, 'paso' => 8]) }}" class="space-y-6">
    @csrf

    <div class="rounded-2xl border border-gray-200 bg-white p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Localización de lesiones</h3>
                <p class="text-sm text-gray-500">Selecciona el tipo (número) y haz clic en el cuerpo para ubicarlo. Puedes agregar varios.</p>
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-gray-700 select-none">
                <input type="checkbox"
                    name="no_aplica_lesiones"
                    id="no_aplica_lesiones"
                    value="1"
                    class="rounded border-gray-300 text-gray-900 focus:ring-gray-900"
                    @checked($noAplica)>
                <span>No aplica</span>
            </label>
        </div>

        {{-- Hidden JSON --}}
        <input type="hidden" name="lesiones" id="lesiones_json" value='{{ $lesionesInicial }}'>

        {{-- 1 col (izq) + 2 col (der) --}}
        <div id="lesiones_wrapper" class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- Panel de tipos (1/3) --}}
            <div class="rounded-2xl border border-gray-200 p-4 bg-white">
                <div class="flex items-center justify-between mb-2">
                    <div class="font-semibold text-gray-900">Tipo de lesión</div>
                    <div class="text-xs text-gray-500">Selecciona uno</div>
                </div>

                @php
                $tipos = [
                1 => 'Herida penetrante',
                2 => 'Herida cortante',
                3 => 'Fractura expuesta',
                4 => 'Fractura cerrada',
                5 => 'Cuerpo extraño',
                6 => 'Hemorragia',
                7 => 'Mordedura',
                8 => 'Picadura',
                9 => 'Excoriación',
                10 => 'Deformidad o masa',
                11 => 'Hematoma',
                12 => 'Eritema / inflamación',
                13 => 'Luxación / esguince',
                14 => 'Quemadura',
                ];
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-2" id="tipo_palette">
                    @foreach($tipos as $num => $label)
                    <button type="button"
                        data-tipo="{{ $num }}"
                        class="tipo-btn text-left w-full rounded-xl border border-gray-200 px-3 py-2 hover:bg-gray-50 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center font-bold text-gray-800">
                            {{ $num }}
                        </span>
                        <span class="text-sm text-gray-800">{{ $label }}</span>
                    </button>
                    @endforeach
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <button type="button" id="btn_clear" class="text-sm px-3 py-2 rounded-xl border bg-white hover:bg-gray-50">
                        Limpiar puntos
                    </button>
                    <div class="text-xs text-gray-500">
                        Click en un punto → eliminar
                    </div>
                </div>
            </div>

            {{-- Lienzo (2/3) --}}
            <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-4 lg:sticky lg:top-6 self-start">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="font-semibold text-gray-900">Ubicación en el cuerpo</div>

                    <div class="flex gap-2">
                        <button type="button" id="tab_front"
                            class="tab-btn px-3 py-2 rounded-xl border bg-gray-900 text-white">
                            Frontal
                        </button>
                        <button type="button" id="tab_back"
                            class="tab-btn px-3 py-2 rounded-xl border bg-white hover:bg-gray-50">
                            Posterior
                        </button>
                    </div>
                </div>

                <div class="text-sm text-gray-500 mb-3">
                    Tipo seleccionado: <span id="tipo_actual_badge" class="font-semibold text-gray-900">—</span>
                </div>

                {{-- Stage más grande + sin recorte brutal --}}
                <div id="body_stage"
                    class="relative w-full overflow-hidden rounded-2xl border border-gray-200 bg-gray-50"
                    style="height: 680px;">
                    <img id="body_img"
                        src="{{ $imgSrc }}"
                        alt="Cuerpo (frontal y posterior)"
                        class="absolute inset-0 w-full h-full select-none pointer-events-none"
                        style="
                            object-fit: contain;      /* ✅ NO recorta */
                            object-position: left center;
                            transform: none;          /* ✅ nada de zoom raro */
                         ">
                    {{-- Marcadores se insertan por JS --}}
                </div>

                <div class="mt-3 text-xs text-gray-500">
                    Consejo: selecciona el número, luego clic donde corresponde. Si te equivocas, clic en el punto para borrarlo.
                </div>

                {{-- Resumen --}}
                <div class="mt-4 rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2 text-sm font-semibold text-gray-900 flex items-center justify-between">
                        <span>Resumen</span>
                        <span class="text-xs text-gray-500"><span id="count_pts">0</span> puntos</span>
                    </div>
                    <div class="p-4 text-sm text-gray-700" id="list_pts">
                        <div class="text-gray-500">Aún no hay lesiones marcadas.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const chkNoAplica = document.getElementById('no_aplica_lesiones');
        const wrapper = document.getElementById('lesiones_wrapper');
        const stage = document.getElementById('body_stage');
        const palette = document.getElementById('tipo_palette');
        const inputJson = document.getElementById('lesiones_json');
        const badge = document.getElementById('tipo_actual_badge');
        const listPts = document.getElementById('list_pts');
        const countPts = document.getElementById('count_pts');

        const tabFront = document.getElementById('tab_front');
        const tabBack = document.getElementById('tab_back');
        const btnClear = document.getElementById('btn_clear');

        let currentView = 'front';
        let selectedTipo = null;

        // Estado
        let puntos = [];
        try {
            puntos = JSON.parse(inputJson.value || '[]');
            if (!Array.isArray(puntos)) puntos = [];
        } catch (e) {
            puntos = [];
        }

        function saveJson() {
            inputJson.value = JSON.stringify(puntos);
        }

        function setDisabled(disabled) {
            wrapper.classList.toggle('opacity-50', disabled);
            wrapper.classList.toggle('pointer-events-none', disabled);

            if (disabled) {
                puntos = [];
                selectedTipo = null;
                badge.textContent = '—';
                saveJson();
                render();
            }
        }

        const bodyImg = document.getElementById('body_img');

        function setTab(view) {
            currentView = view;

            const isFront = view === 'front';
            tabFront.classList.toggle('bg-gray-900', isFront);
            tabFront.classList.toggle('text-white', isFront);
            tabBack.classList.toggle('bg-gray-900', !isFront);
            tabBack.classList.toggle('text-white', !isFront);

            // izquierda = frontal, derecha = posterior
            if (bodyImg) {
                bodyImg.style.objectPosition = isFront ? 'left center' : 'right center';
            }

            render();
        }

        tabFront.addEventListener('click', () => setTab('front'));
        tabBack.addEventListener('click', () => setTab('back'));

        // Selección de tipo
        palette.querySelectorAll('.tipo-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tipo = parseInt(btn.dataset.tipo, 10);
                selectedTipo = tipo;
                badge.textContent = tipo;

                palette.querySelectorAll('.tipo-btn').forEach(b => {
                    b.classList.remove('border-gray-900', 'bg-gray-50');
                    b.classList.add('border-gray-200');
                });
                btn.classList.add('border-gray-900', 'bg-gray-50');
                btn.classList.remove('border-gray-200');
            });
        });

        // Click para crear punto
        stage.addEventListener('click', (e) => {
            if (chkNoAplica.checked) return;
            if (!selectedTipo) {
                alert('Selecciona un tipo (número) de lesión primero.');
                return;
            }

            const rect = stage.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width; // 0..1
            const y = (e.clientY - rect.top) / rect.height; // 0..1

            // ✅ Regla: Frontal = mitad izquierda, Posterior = mitad derecha
            const isLeftHalf = x <= 0.5;

            if (currentView === 'front' && !isLeftHalf) {
                alert('Estás en "Frontal". Marca en el lado izquierdo (frontal) o cambia a "Posterior".');
                return;
            }
            if (currentView === 'back' && isLeftHalf) {
                alert('Estás en "Posterior". Marca en el lado derecho (posterior) o cambia a "Frontal".');
                return;
            }

            // Normalizamos x dentro de su mitad (0..1 en la mitad correspondiente)
            const xNorm = currentView === 'front' ?
                (x / 0.5) :
                ((x - 0.5) / 0.5);

            puntos.push({
                view: currentView,
                x: +xNorm.toFixed(4), // ahora es relativo a su mitad
                y: +y.toFixed(4),
                tipo: selectedTipo
            });

            saveJson();
            render();
        });


        function removePoint(indexGlobal) {
            puntos.splice(indexGlobal, 1);
            saveJson();
            render();
        }

        function render() {
            stage.querySelectorAll('[data-marker="1"]').forEach(n => n.remove());

            const visibles = puntos
                .map((p, idx) => ({
                    ...p,
                    _idx: idx
                }))
                .filter(p => p.view === currentView);

            visibles.forEach(p => {
                const el = document.createElement('button');
                el.type = 'button';
                el.dataset.marker = "1";
                el.className = "absolute -translate-x-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-gray-900 text-white text-sm font-bold shadow";
                const left = (p.view === 'front') ?
                    (p.x * 50) // 0..50%
                    :
                    (50 + p.x * 50); // 50..100%

                el.style.left = left + '%';

                el.style.top = (p.y * 100) + '%';
                el.textContent = p.tipo;

                el.title = 'Click para eliminar';
                el.addEventListener('click', (ev) => {
                    ev.stopPropagation();
                    removePoint(p._idx);
                });

                stage.appendChild(el);
            });

            countPts.textContent = String(puntos.length);

            if (puntos.length === 0) {
                listPts.innerHTML = '<div class="text-gray-500">Aún no hay lesiones marcadas.</div>';
                return;
            }

            const rows = puntos.map((p, i) => `
            <div class="flex items-center justify-between gap-3 py-2 border-b last:border-b-0">
                <div class="text-sm">
                    <span class="font-semibold">#${i+1}</span>
                    · Tipo <span class="font-semibold">${p.tipo}</span>
                    · Vista <span class="font-semibold">${p.view === 'front' ? 'Frontal' : 'Posterior'}</span>
                </div>
                <button type="button" class="text-sm px-3 py-1 rounded-lg border hover:bg-gray-50" data-del="${i}">
                    Eliminar
                </button>
            </div>
        `).join('');

            listPts.innerHTML = rows;
            listPts.querySelectorAll('[data-del]').forEach(btn => {
                btn.addEventListener('click', () => removePoint(parseInt(btn.dataset.del, 10)));
            });
        }

        btnClear.addEventListener('click', () => {
            if (!confirm('¿Limpiar todos los puntos?')) return;
            puntos = [];
            saveJson();
            render();
        });

        chkNoAplica.addEventListener('change', () => setDisabled(chkNoAplica.checked));

        // init
        setDisabled(!!chkNoAplica.checked);
        setTab('front');
        saveJson();
        render();
    });
</script>