@extends('layouts.admin')

@section('content')
<div class="p-6 space-y-6 max-w-3xl">

    <div>
        <h1 class="text-2xl font-extrabold">Editar usuario</h1>
        <p class="text-sm text-gray-600 mt-1">Actualiza datos y rol. Password es opcional.</p>
    </div>

    <form method="POST" action="{{ route('admin.usuarios.update', $user) }}" class="bg-white border rounded-2xl p-6 space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-semibold">Username</label>
                <input name="username" value="{{ old('username', $user->username) }}"
                       class="mt-1 w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0"
                       required>
            </div>

            <div>
                <label class="text-sm font-semibold">Nombre</label>
                <input name="name" value="{{ old('name', $user->name) }}"
                       class="mt-1 w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0"
                       required>
            </div>

            <div>
                <label class="text-sm font-semibold">Email (opcional)</label>
                <input name="email" type="email" value="{{ old('email', $user->email) }}"
                       class="mt-1 w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0">
            </div>

            <div>
                <label class="text-sm font-semibold">Rol</label>
                <select name="role" class="mt-1 w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0" required>
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}" @selected(old('role', $user->role) === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-semibold">Nuevo password (opcional)</label>
                <input name="password" type="password"
                       class="mt-1 w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0"
                       minlength="6">
                <p class="text-xs text-gray-500 mt-1">Si lo dejas vacío, no se cambia.</p>
            </div>

            <div class="md:col-span-2 flex items-center gap-2">
                <input id="is_active" name="is_active" type="checkbox" value="1"
                       class="rounded border-gray-300"
                       @checked(old('is_active', $user->is_active))>
                <label for="is_active" class="text-sm font-semibold">Usuario activo</label>
            </div>
        </div>

        <div class="flex gap-2 justify-end">
            <a href="{{ route('admin.usuarios.index') }}"
               class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                Volver
            </a>
            <button class="px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-black text-sm font-semibold">
                Guardar cambios
            </button>
        </div>
    </form>

</div>
@endsection
