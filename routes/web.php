<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\EnfermeroController;

// Página principal (pública)
Route::get('/', function () {
    return redirect('/login');
});


// Rutas de autenticación (Laravel Breeze)
require __DIR__.'/auth.php';

// -------------------
// RUTAS PROTEGIDAS POR ROL
// -------------------

// ADMINISTRADOR
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
});

// MÉDICO
Route::middleware(['auth', 'role:medico'])->group(function () {
    Route::get('/medico/dashboard', [MedicoController::class, 'index'])->name('medico.dashboard');
});

// ENFERMERO
Route::middleware(['auth', 'role:enfermero'])->group(function () {
    Route::get('/enfermeria/dashboard', [EnfermeroController::class, 'index'])->name('enfermero.dashboard');
});

// PERFIL DEL USUARIO (ya viene con Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
