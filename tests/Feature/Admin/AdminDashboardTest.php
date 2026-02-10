<?php

namespace Tests\Feature\Admin;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_dashboard_kpis_are_computed(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-01-08 10:00:00'));

        $admin = $this->createUser('admin');
        $medico = $this->createUser('medico');

        $paciente1 = $this->createPaciente();
        $paciente2 = $this->createPaciente();

        $form1 = $this->createFormulario($medico, $paciente1, ['estado' => 'completo']);
        $form1->created_at = Carbon::now();
        $form1->save();

        $form2 = $this->createFormulario($medico, $paciente2, ['estado' => 'borrador']);
        $form2->created_at = Carbon::now()->subDay();
        $form2->save();

        $form3 = $this->createFormulario($medico, $paciente2, ['estado' => 'archivado']);
        $form3->created_at = Carbon::now()->subDays(2);
        $form3->save();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertViewHas('kpi', function ($kpi) {
            return $kpi['pacientes_total'] === 2
                && $kpi['f_hoy'] === 1
                && $kpi['f_semana'] === 3
                && $kpi['f_mes'] === 3
                && $kpi['completos'] === 1
                && $kpi['borrador'] === 1
                && $kpi['archivados'] === 1;
        });

        Carbon::setTestNow();
    }
}
