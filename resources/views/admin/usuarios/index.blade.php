@extends('layouts.admin')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-extrabold">Usuarios</h1>
            <p class="text-sm text-gray-600 mt-1">Crear, editar, asignar roles y desactivar accesos.</p>
        </div>

        <a href="{{ route('admin.usuarios.create') }}"
           class="px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-black text-sm font-semibold">
            + Crear usuario
        </a>
    </div>

    <form method="GET" class="flex flex-col md:flex-row gap-2">
        <input name="buscar" value="{{ $buscar }}"
               class="w-full md:w-96 rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0"
               placeholder="Buscar por usuario, nombre, email o rol">
        <button class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
            Buscar
        </button>
        @if($buscar)
            <a href="{{ route('admin.usuarios.index') }}"
               class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                Limpiar
            </a>
        @endif
    </form>

    <div class="bg-white border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b text-gray-600">
                    <tr>
                        <th class="text-left px-5 py-3">Usuario</th>
                        <th class="text-left px-5 py-3">Nombre</th>
                        <th class="text-left px-5 py-3">Rol</th>
                        <th class="text-left px-5 py-3">Estado</th>
                        <th class="text-right px-5 py-3">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @foreach($usuarios as $u)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3">
                                <div class="font-semibold">{{ $u->username }}</div>
                                <div class="text-xs text-gray-500">{{ $u->email ?? '—' }}</div>
                            </td>

                            <td class="px-5 py-3">
                                <div class="font-semibold">{{ $u->name }}</div>
                                <div class="text-xs text-gray-500">ID: {{ $u->id }}</div>
                            </td>

                            <td class="px-5 py-3">
                                <span class="inline-flex px-2 py-1 rounded-full border text-xs font-semibold
                                    {{ $u->role === 'admin' ? 'bg-purple-100 text-purple-800 border-purple-200' : '' }}
                                    {{ $u->role === 'medico' ? 'bg-blue-100 text-blue-800 border-blue-200' : '' }}
                                    {{ $u->role === 'enfermero' ? 'bg-green-100 text-green-800 border-green-200' : '' }}
                                ">
                                    {{ strtoupper($u->role) }}
                                </span>
                            </td>

                            <td class="px-5 py-3">
                                @if($u->is_active)
                                    <span class="inline-flex px-2 py-1 rounded-full border text-xs font-semibold bg-green-100 text-green-800 border-green-200">
                                        ACTIVO
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-1 rounded-full border text-xs font-semibold bg-red-100 text-red-800 border-red-200">
                                        DESACTIVADO
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2 flex-wrap">
                                    <a href="{{ route('admin.usuarios.edit', $u) }}"
                                       class="px-3 py-2 rounded-xl border bg-white hover:bg-gray-50 text-xs font-semibold">
                                        Editar
                                    </a>

                                    <form method="POST" action="{{ route('admin.usuarios.reset', $u) }}"
                                          onsubmit="return confirm('¿Resetear password de {{ $u->username }}? Se mostrará un password temporal.');">
                                        @csrf
                                        <button class="px-3 py-2 rounded-xl bg-gray-900 text-white hover:bg-black text-xs font-semibold">
                                            Reset Pass
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.usuarios.toggle', $u) }}"
                                          onsubmit="return confirm('¿Cambiar estado activo/desactivado de {{ $u->username }}?');">
                                        @csrf
                                        @method('PATCH')
                                        <button class="px-3 py-2 rounded-xl border bg-white hover:bg-gray-50 text-xs font-semibold">
                                            {{ $u->is_active ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.usuarios.destroy', $u) }}"
                                          onsubmit="return confirm('¿Eliminar usuario {{ $u->username }}? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-3 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700 text-xs font-semibold">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    @if($usuarios->count() === 0)
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-500">
                                No hay usuarios para mostrar.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t">
            {{ $usuarios->links() }}
        </div>
    </div>

</div>
@endsection
