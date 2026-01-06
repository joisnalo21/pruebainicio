<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminUsuariosController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');

        $usuarios = User::query()
            ->when($buscar, function ($q) use ($buscar) {
                $q->where('username', 'like', "%{$buscar}%")
                  ->orWhere('name', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%")
                  ->orWhere('role', 'like', "%{$buscar}%");
            })
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.usuarios.index', compact('usuarios', 'buscar'));
    }

    public function create()
    {
        $roles = ['admin' => 'Administrador', 'medico' => 'Médico', 'enfermero' => 'Enfermero'];
        return view('admin.usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'name'     => ['required', 'string', 'max:120'],
            'email'    => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'role'     => ['required', 'in:admin,medico,enfermero'],
            'password' => ['required', 'string', 'min:6'],
            'is_active'=> ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        User::create($data); // password se hashea por cast "hashed"

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $roles = ['admin' => 'Administrador', 'medico' => 'Médico', 'enfermero' => 'Enfermero'];
        return view('admin.usuarios.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $user->id],
            'name'     => ['required', 'string', 'max:120'],
            'email'    => ['nullable', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'role'     => ['required', 'in:admin,medico,enfermero'],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active'=> ['nullable', 'boolean'],
        ]);

        // No obligar cambio de password si viene vacío
        if (empty($data['password'])) unset($data['password']);

        $data['is_active'] = $request->boolean('is_active', true);

        // Regla: no te desactives a ti mismo
        if ($user->id === auth()->id() && $data['is_active'] === false) {
            return back()->with('error', 'No puedes desactivarte a ti mismo.')->withInput();
        }

        // Regla: no convertirte en NO-admin si eres el único admin (opcional)
        if ($user->role === 'admin' && ($data['role'] ?? 'admin') !== 'admin') {
            $admins = User::where('role', 'admin')->count();
            if ($admins <= 1) {
                return back()->with('error', 'No puedes quitar el rol admin al único administrador.')->withInput();
            }
        }

        $user->update($data);

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        // No permitir eliminarse a sí mismo
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        // No permitir borrar el último admin
        if ($user->role === 'admin') {
            $admins = User::where('role', 'admin')->count();
            if ($admins <= 1) {
                return back()->with('error', 'No puedes eliminar al único administrador.');
            }
        }

        $user->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }

    public function toggleActivo(User $user)
    {
        // No permitir desactivar al propio usuario
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivarte a ti mismo.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'Estado actualizado: ' . ($user->is_active ? 'Activo' : 'Desactivado'));
    }

    public function resetPassword(User $user)
    {
        // Genera password temporal
        $temp = Str::upper(Str::random(10)) . rand(10, 99);

        $user->password = $temp; // hashed cast lo hashea
        $user->save();

        // Lo mostramos en flash (solo una vez)
        return back()->with('success', "Password reseteado. Nuevo password temporal: {$temp}");
    }
}
