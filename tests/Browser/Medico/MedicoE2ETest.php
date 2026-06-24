<?php

namespace Tests\Browser\Medico;

use App\Models\Formulario008;
use App\Models\Paciente;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Support\CreatesTestData;

class MedicoE2ETest extends DuskTestCase
{
    use CreatesTestData;

    // Debe coincidir con la contraseña de UsuariosSeeder.
    private const PASSWORD = 'Hospital2025*';

    private ?Paciente $pacientePrueba = null;

    /**
     * NO se usa DatabaseMigrations: estas pruebas corren contra la MySQL real
     * (la misma BD que la app desplegada), por lo que migrate:fresh borraría
     * los usuarios sembrados. En su lugar, limpiamos SOLO lo que creó la prueba.
     */
    protected function tearDown(): void
    {
        if ($this->pacientePrueba) {
            Formulario008::where('paciente_id', $this->pacientePrueba->id)->delete();
            $this->pacientePrueba->delete();
            $this->pacientePrueba = null;
        }

        parent::tearDown();
    }

    private function loginComoMedico(Browser $browser): void
    {
        $this->limpiarSesion($browser)
            ->visit('/login')
            ->waitFor('#username')
            ->type('username', 'drnavia')
            ->type('password', self::PASSWORD)
            ->press('Iniciar sesión')
            ->waitForLocation('/medico/dashboard');
    }

    /**
     * EXCLUIDO DEL ALCANCE E2E (decisión documentada): el formulario de
     * registro de paciente usa selects encadenados poblados por JavaScript
     * (provincias.json + API de nacionalidad), no deterministas bajo Selenium.
     * Su validación se cubre con pruebas Feature.
     */
    public function test_medico_puede_crear_paciente(): void
    {
        $this->markTestSkipped(
            'Formulario de paciente con selects dinámicos por JavaScript: '
            .'excluido del alcance de E2E determinista (cubierto por pruebas Feature).'
        );
    }

    public function test_medico_puede_iniciar_formulario_desde_seleccionar_paciente(): void
    {
        // Garantiza que el médico sembrado exista (lo crea UsuariosSeeder).
        User::where('username', 'drnavia')->firstOrFail();

        $this->pacientePrueba = $this->createPaciente([
            'cedula' => $this->generarCedulaValida('13'),
            'primer_nombre' => 'Carlos',
            'apellido_paterno' => 'Gomez',
            'telefono' => '0999999999',
        ]);

        $paciente = $this->pacientePrueba;

        $this->browse(function (Browser $browser) use ($paciente) {
            $this->loginComoMedico($browser);

            $browser->visit('/medico/formularios/nuevo')
                ->waitFor('input[name="q"]')
                ->type('q', $paciente->cedula)
                ->press('🔎 Buscar')
                ->waitForText($paciente->cedula)
                ->press('Iniciar 008 →')
                ->waitForText('Formulario 008 creado en borrador.')
                ->assertPathBeginsWith('/medico/formularios/')
                ->assertPathEndsWith('/paso/1');
        });
    }

    // ---- Helpers de cédula (para createPaciente) ----

    private function calcularDigitoVerificador(string $base9): int
    {
        $coef = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $d = (int) $base9[$i] * $coef[$i];
            if ($d >= 10) {
                $d -= 9;
            }
            $sum += $d;
        }
        $mod = $sum % 10;

        return $mod === 0 ? 0 : (10 - $mod);
    }

    private function generarCedulaValida(string $provincia = '13'): string
    {
        $provincia = str_pad($provincia, 2, '0', STR_PAD_LEFT);
        $resto = str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
        $base9 = $provincia.$resto;
        $dv = $this->calcularDigitoVerificador($base9);

        return $base9.$dv;
    }
}