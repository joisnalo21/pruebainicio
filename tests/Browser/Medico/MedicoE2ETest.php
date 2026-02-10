<?php

namespace Tests\Browser\Medico;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Tests\Support\CreatesTestData;

class MedicoE2ETest extends DuskTestCase
{
    use DatabaseMigrations;
    use CreatesTestData;

    private function loginAs(Browser $browser, User $user): void
    {
        $browser->visit('/login')
            ->type('username', $user->username)
            ->type('password', 'password')
            ->press('Iniciar sesión')
            ->waitForLocation('/medico/dashboard');
    }

    private function calcularDigitoVerificador(string $base9): int
    {
        $coef = [2, 1, 2, 1, 2, 1, 2, 1, 2];
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $d = (int) $base9[$i] * $coef[$i];
            if ($d >= 10) $d -= 9;
            $sum += $d;
        }
        $mod = $sum % 10;
        return $mod === 0 ? 0 : (10 - $mod);
    }

    private function generarCedulaValida(string $provincia = '13'): string
    {
        $provincia = str_pad($provincia, 2, '0', STR_PAD_LEFT);
        $resto = str_pad((string) random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
        $base9 = $provincia . $resto;
        $dv = $this->calcularDigitoVerificador($base9);
        return $base9 . $dv;
    }

    public function test_medico_puede_crear_paciente(): void
    {
        $medico = User::factory()->medico()->create([
            'username' => 'medico_e2e',
            'password' => 'password',
        ]);

        $cedula = $this->generarCedulaValida('13');

        $this->browse(function (Browser $browser) use ($medico, $cedula) {
            $this->loginAs($browser, $medico);

            $browser->visit('/medico/pacientes/nuevo')
                ->waitFor('#cedula')
                ->type('cedula', $cedula)
                ->type('primer_nombre', 'Ana')
                ->type('segundo_nombre', 'Maria')
                ->type('apellido_paterno', 'Lopez')
                ->type('apellido_materno', 'Perez')
                ->type('lugar_nacimiento', 'Jipijapa')
                ->waitFor('select#nacionalidad option[value="Ecuador"]')
                ->select('nacionalidad', 'Ecuador')
                ->type('fecha_nacimiento', '2000-01-01')
                ->select('sexo', 'Masculino')
                ->type('ocupacion', 'Estudiante')
                ->type('grupo_cultural', 'Mestizo')
                ->select('estado_civil', 'Soltero/a')
                ->type('instruccion', 'Secundaria')
                ->type('empresa', 'N/A')
                ->type('seguro_salud', 'Ninguno')
                ->waitFor('select#provincia option[value="13"]')
                ->select('provincia', '13')
                ->waitFor('select#canton option[value="01"]')
                ->select('canton', '01')
                ->waitFor('select#parroquia option[value="01"]')
                ->select('parroquia', '01')
                ->select('zona', 'Urbana')
                ->type('barrio', 'Centro')
                ->type('direccion', 'Av. Principal')
                ->type('telefono', '0999999999')
                ->press('Guardar paciente')
                ->waitForLocation('/medico/pacientes')
                ->assertPathIs('/medico/pacientes')
                ->assertSee('Paciente registrado correctamente.');
        });
    }

    public function test_medico_puede_iniciar_formulario_desde_seleccionar_paciente(): void
    {
        $medico = User::factory()->medico()->create([
            'username' => 'medico_form',
            'password' => 'password',
        ]);

        $paciente = $this->createPaciente([
            'cedula' => $this->generarCedulaValida('13'),
            'primer_nombre' => 'Carlos',
            'apellido_paterno' => 'Gomez',
            'telefono' => '0999999999',
        ]);

        $this->browse(function (Browser $browser) use ($medico, $paciente) {
            $this->loginAs($browser, $medico);

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
}
