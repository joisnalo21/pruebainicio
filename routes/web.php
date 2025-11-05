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
Route::middleware(['auth', 'role:medico'])->prefix('medico')->group(function () {
    // Dashboard principal
    Route::get('/dashboard', [MedicoController::class, 'index'])->name('medico.dashboard');

    // -------------------
    // PACIENTES
    // -------------------
    Route::get('/pacientes', [MedicoController::class, 'listarPacientes'])->name('medico.pacientes');
    Route::get('/pacientes/nuevo', [MedicoController::class, 'crearPaciente'])->name('medico.paciente.nuevo');
    Route::post('/pacientes/guardar', [MedicoController::class, 'guardarPaciente'])->name('medico.paciente.guardar');

    // -------------------
    // FORMULARIOS 008
    // -------------------
    Route::get('/formularios', [MedicoController::class, 'listarFormularios'])->name('medico.formularios');
    Route::get('/formularios/nuevo/{paciente}', [MedicoController::class, 'crearFormulario'])->name('medico.formulario.nuevo');
    Route::post('/formularios/guardar', [MedicoController::class, 'guardarFormulario'])->name('medico.formulario.guardar');
    Route::get('/formularios/editar/{id}', [MedicoController::class, 'editarFormulario'])->name('medico.formulario.editar');
    Route::post('/formularios/actualizar/{id}', [MedicoController::class, 'actualizarFormulario'])->name('medico.formulario.actualizar');

    // -------------------
    // REPORTES MÉDICOS
    // -------------------
    Route::get('/reportes', [MedicoController::class, 'reportes'])->name('medico.reportes');
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
