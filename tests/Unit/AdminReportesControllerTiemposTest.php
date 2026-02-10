<?php

namespace Tests\Unit;

use App\Http\Controllers\AdminReportesController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesTestData;
use Tests\TestCase;

class AdminReportesControllerTiemposTest extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;

    private function callPrivate(object $obj, string $method, array $args = [])
    {
        $ref = new \ReflectionClass($obj);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);
        return $m->invokeArgs($obj, $args);
    }

    public function test_report_tiempos_uses_fallback_note_and_maps_rows(): void
    {
        $medico = $this->createUser('medico', ['name' => 'Tiempo Medico']);

        Schema::shouldReceive('hasColumn')
            ->once()
            ->andReturn(false);

        $fakeQuery = new class($medico->id) {
            public function __construct(private int $userId)
            {
            }

            public function where(...$args)
            {
                return $this;
            }

            public function whereNotNull(...$args)
            {
                return $this;
            }

            public function selectRaw(...$args)
            {
                return $this;
            }

            public function groupBy(...$args)
            {
                return $this;
            }

            public function orderByDesc(...$args)
            {
                return $this;
            }

            public function get()
            {
                return collect([
                    (object) [
                        'created_by' => $this->userId,
                        'total' => 2,
                        'avg_min' => 10.5,
                        'max_min' => 30,
                    ],
                ]);
            }
        };

        $controller = new AdminReportesController();
        $report = $this->callPrivate($controller, 'reportTiempos', [$fakeQuery, []]);

        $this->assertIsArray($report);
        $this->assertStringContainsString('No existe completed_at', $report['note']);
        $this->assertSame('Tiempo Medico (MEDICO)', $report['rows'][0][0]);
        $this->assertSame(2, $report['rows'][0][1]);
        $this->assertSame(10.5, $report['rows'][0][2]);
        $this->assertSame(30, $report['rows'][0][3]);
    }
}
