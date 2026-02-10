<?php

namespace Tests\Unit;

use App\Http\Controllers\AdminReportesController;
use Illuminate\Support\Collection;
use Tests\TestCase;

class AdminReportesControllerProduccionTest extends TestCase
{
    private function callPrivate(object $obj, string $method, array $args = [])
    {
        $ref = new \ReflectionClass($obj);
        $m = $ref->getMethod($method);
        $m->setAccessible(true);
        return $m->invokeArgs($obj, $args);
    }

    private function fakeQuery(array $rows): object
    {
        return new class($rows) {
            public function __construct(private array $rows)
            {
            }

            public function selectRaw(...$args)
            {
                return $this;
            }

            public function groupBy(...$args)
            {
                return $this;
            }

            public function orderBy(...$args)
            {
                return $this;
            }

            public function get(): Collection
            {
                return collect($this->rows);
            }
        };
    }

    public function test_report_produccion_group_week_builds_expected_title_and_totals(): void
    {
        $controller = new AdminReportesController();
        $query = $this->fakeQuery([
            (object) ['grp' => '202501', 'total' => 3, 'completos' => 2, 'borradores' => 1, 'archivados' => 0],
        ]);

        $report = $this->callPrivate($controller, 'reportProduccion', [$query, ['group' => 'week']]);

        $this->assertStringContainsString('por semana', $report['title']);
        $this->assertSame(3, $report['totals'][1]);
        $this->assertSame(2, $report['totals'][2]);
    }

    public function test_report_produccion_group_month_builds_expected_title_and_totals(): void
    {
        $controller = new AdminReportesController();
        $query = $this->fakeQuery([
            (object) ['grp' => '2025-01', 'total' => 1, 'completos' => 0, 'borradores' => 0, 'archivados' => 1],
        ]);

        $report = $this->callPrivate($controller, 'reportProduccion', [$query, ['group' => 'month']]);

        $this->assertStringContainsString('por mes', $report['title']);
        $this->assertSame(1, $report['totals'][1]);
        $this->assertSame(0, $report['totals'][2]);
    }
}
