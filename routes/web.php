<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminUsuariosController;
use App\Http\Controllers\AdminFormularioController;
use App\Http\Controllers\AdminPacienteController;
use App\Http\Controllers\AdminReportesController;
use App\Http\Controllers\MedicoController;
use App\Http\Controllers\EnfermeroController;
use App\Http\Controllers\MedicoPacienteController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\EnfermeriaFormulario008Controller;

// Página principal (pública)
Route::get('/', function () {
    return redirect('/login');
});

// Rutas de autenticación (Laravel Breeze)
require __DIR__ . '/auth.php';

// -------------------
// RUTAS PROTEGIDAS POR ROL
// -------------------

//  ADMINISTRADOR

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

        // =========================
        // USUARIOS (CRUD)
        // =========================
        Route::get('/usuarios', [AdminUsuariosController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/crear', [AdminUsuariosController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [AdminUsuariosController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/{user}/editar', [AdminUsuariosController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{user}', [AdminUsuariosController::class, 'update'])->name('usuarios.update');
        Route::delete('/usuarios/{user}', [AdminUsuariosController::class, 'destroy'])->name('usuarios.destroy');

        // Opcionales (pero recomendados)
        Route::patch('/usuarios/{user}/toggle', [AdminUsuariosController::class, 'toggleActivo'])->name('usuarios.toggle');
        Route::post('/usuarios/{user}/reset-password', [AdminUsuariosController::class, 'resetPassword'])->name('usuarios.reset');

        // ADMIN - FORMULARIOS 008
        Route::get('/formularios', [AdminFormularioController::class, 'index'])->name('formularios.index');
        Route::get('/formularios/{formulario}', [AdminFormularioController::class, 'show'])->name('formularios.show');
        Route::get('/formularios/{formulario}/ver/paso/{paso}', [AdminFormularioController::class, 'verPaso'])
            ->whereNumber('paso')
            ->name('formularios.ver.paso');

        Route::get('/formularios/{formulario}/pdf', [AdminFormularioController::class, 'pdf'])->name('formularios.pdf');

        Route::patch('/formularios/{formulario}/archivar', [AdminFormularioController::class, 'archivar'])->name('formularios.archivar');
        Route::patch('/formularios/{formulario}/desarchivar', [AdminFormularioController::class, 'desarchivar'])->name('formularios.desarchivar');

        Route::delete('/formularios/{formulario}', [AdminFormularioController::class, 'destroy'])->name('formularios.destroy');


        // ADMIN - PACIENTES (solo lectura)
        Route::get('/pacientes', [AdminPacienteController::class, 'index'])->name('pacientes.index');
        Route::get('/pacientes/{paciente}', [AdminPacienteController::class, 'show'])->name('pacientes.show');


        // ADMIN - REPORTES
        Route::get('/reportes', [AdminReportesController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/pdf', [AdminReportesController::class, 'pdf'])->name('reportes.pdf');
    });







// MÉDICO
Route::middleware(['auth', 'role:medico'])
    ->prefix('medico')
    ->name('medico.')
    ->group(function () {
        Route::get('/debug/db', function () {
            $driver = DB::connection()->getDriverName();
            if ($driver === 'sqlite') {
                $database = DB::connection()->getDatabaseName();
                $server = null;
            } else {
                $database = DB::select('select database() as db')[0]->db ?? null;
                $server = DB::select('select @@hostname as host, @@port as port')[0] ?? null;
            }

            return response()->json([
                'database' => $database,
                'server'   => $server,
                'has_no_aplica_apartado_3' => Schema::hasColumn('formularios008', 'no_aplica_apartado_3'),
                'has_custodia_policial'    => Schema::hasColumn('formularios008', 'custodia_policial'),
            ]);
        })->name('debug.db');





        // DASHBOARD
        Route::get('/dashboard', [MedicoController::class, 'index'])->name('dashboard');

        // PACIENTES (CRUD)
        Route::get('/pacientes/validar-cedula/{cedula}', [MedicoPacienteController::class, 'validarCedula'])
            ->name('pacientes.validarCedula');

        Route::resource('pacientes', MedicoPacienteController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

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


        // Vista SOLO LECTURA (solo completos)
        Route::get('/formularios/{formulario}/ver/paso/{paso}', [MedicoController::class, 'verPaso'])
            ->whereNumber('paso')
            ->name('formularios.ver.paso');

        // REPORTES
        Route::get('/reportes', [MedicoController::class, 'reportes'])->name('reportes');
    });


// ENFERMERÍA

Route::middleware(['auth', 'role:enfermero'])
    ->prefix('enfermeria')
    ->name('enfermero.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [EnfermeroController::class, 'index'])
            ->name('dashboard');

        // =========================
        // Pacientes (CRUD)
        // =========================

        Route::get('/pacientes/validar-cedula/{cedula}', [MedicoPacienteController::class, 'validarCedula'])
            ->name('pacientes.validarCedula');

        Route::resource('pacientes', MedicoPacienteController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

        // =========================
        // Formularios 008 (consulta)
        // =========================
        Route::get('/formularios', [EnfermeriaFormulario008Controller::class, 'index'])
            ->name('formularios.index');

        // Vista resumen (solo lectura)
        Route::get('/formularios/{formulario}/resumen', [EnfermeriaFormulario008Controller::class, 'resumen'])
            ->name('formularios.resumen');

        //ver paso
        Route::get('/formularios/{formulario}/ver/paso/{paso}', [EnfermeriaFormulario008Controller::class, 'verPaso'])
            ->whereNumber('paso')
            ->name('formularios.ver.paso');


        // PDF (solo completos)
        Route::get('/formularios/{formulario}/pdf', [EnfermeriaFormulario008Controller::class, 'pdf'])
            ->name('formularios.pdf');


        Route::get('/formularios/{formulario}/ver/paso/{paso}', [EnfermeriaFormulario008Controller::class, 'verPaso'])
            ->whereNumber('paso')
            ->name('formularios.ver.paso');
    });


// Breeze / Auth tests esperan que exista la ruta nombrada "dashboard"
Route::middleware(['auth'])->get('/dashboard', function () {
    $role = auth()->user()->role ?? null;

    return match ($role) {
        'admin'     => redirect()->route('admin.dashboard'),
        'medico'    => redirect()->route('medico.dashboard'),
        'enfermero' => redirect()->route('enfermero.dashboard'),
        default     => abort(403, 'Rol no válido'),
    };
})->name('dashboard');

// PERFIL DEL USUARIO (Laravel Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

