<?php

namespace Tests\Feature\Formulario008;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class ArchivarTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_puede_archivar_formulario_borrador(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 10, 12, 0, 0));

        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['estado' => 'borrador']);

        $response = $this->actingAs($medico)->patch("/medico/formularios/{$form->id}/archivar");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $form->refresh();
        $this->assertSame('archivado', $form->estado);
        $this->assertNotNull($form->archivado_en);
    }

    public function test_no_puede_archivar_formulario_completo(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['estado' => 'completo']);

        $response = $this->actingAs($medico)->patch("/medico/formularios/{$form->id}/archivar");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $form->refresh();
        $this->assertSame('completo', $form->estado);
    }

    public function test_desarchivar_requiere_que_este_archivado(): void
    {
        $medico = $this->createUser('medico');
        $form = $this->createFormulario($medico, null, ['estado' => 'borrador']);

        $this->actingAs($medico)
            ->patch("/medico/formularios/{$form->id}/desarchivar")
            ->assertRedirect()
            ->assertSessionHas('error');

        $form->refresh();
        $this->assertSame('borrador', $form->estado);

        // Ahora sí: archivado
        $form->update(['estado' => 'archivado', 'archivado_en' => now()]);

        $this->actingAs($medico)
            ->patch("/medico/formularios/{$form->id}/desarchivar")
            ->assertRedirect()
            ->assertSessionHas('success');

        $form->refresh();
        $this->assertSame('borrador', $form->estado);
        $this->assertNull($form->archivado_en);
    }
}
