<?php

namespace Tests\Feature\Formulario008;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class ListadoOcultaArchivadosTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    public function test_por_defecto_no_muestra_archivados(): void
    {
        $medico = $this->createUser('medico');

        // Un borrador y un archivado
        $this->createFormulario($medico, null, ['estado' => 'borrador']);
        $this->createFormulario($medico, null, ['estado' => 'archivado', 'archivado_en' => now()]);

        $response = $this->actingAs($medico)->get('/medico/formularios');

        $response->assertOk();
        $response->assertViewHas('formularios', function ($p) {
            return $p instanceof LengthAwarePaginator && $p->total() === 1;
        });
    }

    public function test_filtrar_por_archivado_muestra_solo_archivados(): void
    {
        $medico = $this->createUser('medico');

        $this->createFormulario($medico, null, ['estado' => 'borrador']);
        $this->createFormulario($medico, null, ['estado' => 'archivado', 'archivado_en' => now()]);

        $response = $this->actingAs($medico)->get('/medico/formularios?estado=archivado');

        $response->assertOk();
        $response->assertViewHas('formularios', function ($p) {
            return $p instanceof LengthAwarePaginator && $p->total() === 1;
        });
    }
}
