@extends('layouts.admin')

@section('content')
<div class="p-6 space-y-6 max-w-3xl">

    <div>
        <h1 class="text-2xl font-extrabold">Crear usuario</h1>
        <p class="text-sm text-gray-600 mt-1">Crea un usuario y asigna su rol (admin, médico o enfermero).</p>
    </div>

    <form method="POST" action="{{ route('admin.usuarios.store') }}" class="bg-white border rounded-2xl p-6 space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-semibold">Username</label>
                <input name="username" value="{{ old('username') }}"
                       class="mt-1 w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0"
                       required>
            </div>

            <div>
                <label class="text-sm font-semibold">Nombre</label>
                <input name="name" value="{{ old('name') }}"
                       class="mt-1 w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0"
                       required>
            </div>

            <div>
                <label class="text-sm font-semibold">Email (opcional)</label>
                <input name="email" type="email" value="{{ old('email') }}"
                       class="mt-1 w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0">
            </div>

            <div>
                <label class="text-sm font-semibold">Rol</label>
                <select name="role" class="mt-1 w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0" required>
                    @foreach($roles as $key => $label)
                        <option value="{{ $key }}" @selected(old('role', 'medico') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-semibold">Password</label>
                <input name="password" type="password"
                       class="mt-1 w-full rounded-xl border-gray-300 focus:border-gray-900 focus:ring-0"
                       required minlength="6">
                <p class="text-xs text-gray-500 mt-1">Mínimo 6 caracteres.</p>
            </div>

            <div class="md:col-span-2 flex items-center gap-2">
                <input id="is_active" name="is_active" type="checkbox" value="1"
                       class="rounded border-gray-300"
                       @checked(old('is_active', true))>
                <label for="is_active" class="text-sm font-semibold">Usuario activo</label>
            </div>
        </div>

        <div class="flex gap-2 justify-end">
            <a href="{{ route('admin.usuarios.index') }}"
               class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold">
                Cancelar
            </a>
            <button class="px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-black text-sm font-semibold">
                Guardar
            </button>
        </div>
    </form>

</div>
@endsection
