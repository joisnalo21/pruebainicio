<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
{
    $credentials = $request->validate([
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();

        // Redirigir según el rol
        switch (Auth::user()->role) {
            case 'admin':
                return redirect()->intended('/admin/dashboard');
            case 'medico':
                return redirect()->intended('/medico/dashboard');
            case 'enfermero':
                return redirect()->intended('/enfermeria/dashboard');
            default:
                Auth::logout();
                return redirect('/login')->withErrors('Rol no válido.');
        }
    }

    return back()->withErrors([
        'username' => 'Las credenciales no son correctas.',
    ]);
}


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
