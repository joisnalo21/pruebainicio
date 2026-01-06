<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\EnfermeroController;
use App\Http\Controllers\MedicoPacienteController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Página principal (pública)
Route::get('/', function () {
    return redirect('/login');
});

// Rutas de autenticación (Laravel Breeze)
require __DIR__ . '/auth.php';

// -------------------
// RUTAS PROTEGIDAS POR ROL
// -------------------

// ADMINISTRADOR
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
});

// MÉDICO
Route::middleware(['auth', 'role:medico'])
    ->prefix('medico')
    ->name('medico.')
    ->group(function () {
        Route::get('/debug/db', function () {
            return response()->json([
                'database' => DB::select('select database() as db')[0]->db ?? null,
                'server'   => DB::select('select @@hostname as host, @@port as port')[0] ?? null,
                'has_no_aplica_apartado_3' => Schema::hasColumn('formularios008', 'no_aplica_apartado_3'),
                'has_custodia_policial'    => Schema::hasColumn('formularios008', 'custodia_policial'),
            ]);
        })->name('debug.db');





        // DASHBOARD
        Route::get('/dashboard', [MedicoController::class, 'index'])->name('dashboard');

        // PACIENTES (CRUD)
        Route::get('/pacientes/validar-cedula/{cedula}', [MedicoPacienteController::class, 'validarCedula'])
            ->name('pacientes.validarCedula');

        Route::get('/pacientes', [MedicoPacienteController::class, 'index'])
            ->name('pacientes.index');

        Route::get('/pacientes/nuevo', [MedicoPacienteController::class, 'create'])
            ->name('pacientes.create');

        Route::post('/pacientes', [MedicoPacienteController::class, 'store'])
            ->name('pacientes.store');

        Route::get('/pacientes/{paciente}/editar', [MedicoPacienteController::class, 'edit'])
            ->name('pacientes.edit');

        Route::put('/pacientes/{paciente}', [MedicoPacienteController::class, 'update'])
            ->name('pacientes.update');

        Route::delete('/pacientes/{paciente}', [MedicoPacienteController::class, 'destroy'])
            ->name('pacientes.destroy');

        // ----------------------------------------------------
        // FORMULARIOS 008 (FLUJO NUEVO: Selección -> Borrador -> Wizard)
        // ----------------------------------------------------

        // Listado principal de formularios
        Route::get('/formularios', [MedicoController::class, 'listarFormularios'])
            ->name('formularios');

        // Seleccionar paciente antes de crear 008
        Route::get('/formularios/nuevo', [MedicoController::class, 'seleccionarPaciente'])
            ->name('formularios.nuevo');

        // Crear el formulario en borrador
        Route::post('/formularios/iniciar', [MedicoController::class, 'iniciarFormulario008'])
            ->name('formularios.iniciar');

        // Wizard por pasos (temporal por ahora)
        Route::get('/formularios/{formulario}/paso/{paso}', [MedicoController::class, 'wizardPaso'])
            ->name('formularios.paso');

        Route::post('/formularios/{formulario}/paso/{paso}', [MedicoController::class, 'guardarPaso'])
            ->name('formularios.paso.store');

        // PDF (solo completos)
        Route::get('/formularios/{formulario}/pdf', [MedicoController::class, 'pdf'])
            ->name('formularios.pdf');


        // Archivar (solo incompletos)
        Route::patch('/formularios/{formulario}/archivar', [MedicoController::class, 'archivar'])
            ->name('formularios.archivar');

        Route::patch('/formularios/{formulario}/desarchivar', [MedicoController::class, 'desarchivar'])
            ->name('formularios.desarchivar');



        // REPORTES
        Route::get('/reportes', [MedicoController::class, 'reportes'])->name('reportes');
    });

// ENFERMERO
Route::middleware(['auth', 'role:enfermero'])->group(function () {
    Route::get('/enfermeria/dashboard', [EnfermeroController::class, 'index'])
        ->name('enfermero.dashboard');
});

// PERFIL DEL USUARIO (Laravel Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
