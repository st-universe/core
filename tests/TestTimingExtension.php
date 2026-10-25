<?php

declare(strict_types=1);

namespace Stu;

use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Event;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\Tracer\Tracer;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use RuntimeException;

final class TestTimingExtension implements Extension
{
    #[\Override]
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        if (getenv('STU_TEST_TIMINGS') !== 'true') {
            return;
        }

        $facade->replaceProgressOutput();
        $facade->registerTracer(new class (dirname(__DIR__) . '/build/test-times.csv') implements Tracer {
            private array $started = [];
            private array $status = [];
            private array $prepared = [];
            private array $rows = [];

            public function __construct(private string $csvFile) {}

            #[\Override]
            public function trace(Event $event): void
            {
                if ($event instanceof PreparationStarted) {
                    $id = $event->test()->id();
                    $this->started[$id] = $event->telemetryInfo()->time();
                    $this->status[$id] = 'OK';
                } elseif ($event instanceof Prepared) {
                    $this->prepared[$event->test()->id()] = true;
                } elseif ($event instanceof Failed || $event instanceof Errored || $event instanceof Skipped || $event instanceof MarkedIncomplete) {
                    $id = $event->test()->id();
                    $this->status[$id] = match (true) {
                        $event instanceof Skipped => 'ÜBERSPRUNGEN',
                        $event instanceof MarkedIncomplete => 'UNVOLLSTÄNDIG',
                        default => 'FEHLER'
                    };
                    if (!isset($this->prepared[$id])) {
                        $this->finish($event->test(), $event->telemetryInfo()->time());
                    }
                } elseif ($event instanceof ConsideredRisky) {
                    $this->status[$event->test()->id()] = 'RISKANT';
                } elseif ($event instanceof Finished && isset($this->started[$event->test()->id()])) {
                    $this->finish($event->test(), $event->telemetryInfo()->time());
                } elseif ($event instanceof ExecutionFinished) {
                    $this->writeReport();
                }
            }

            private function finish(Test $test, HRTime $time): void
            {
                $id = $test->id();
                $started = $this->started[$id] ?? $time;
                $this->record($test, $time->duration($started)->asFloat());
                unset($this->started[$id], $this->prepared[$id]);
            }

            private function record(Test $test, float $seconds): void
            {
                $id = $test->id();
                $status = $this->status[$id] ?? 'OK';
                $this->rows[] = ['seconds' => $seconds, 'status' => $status, 'test' => $id, 'file' => $test->file(), 'line' => $test->isTestMethod() ? $test->line() : 0];
                fwrite(STDOUT, sprintf("%9.3f s  %-15s %s\n", $seconds, $status, $id));
            }

            private function writeReport(): void
            {
                if (!is_dir(dirname($this->csvFile)) && !mkdir(dirname($this->csvFile), 0o755, true)) {
                    throw new RuntimeException('Verzeichnis für die Testzeiten konnte nicht angelegt werden');
                }
                $file = fopen($this->csvFile, 'w');
                if ($file === false) {
                    throw new RuntimeException('CSV-Datei für die Testzeiten konnte nicht geöffnet werden');
                }

                usort($this->rows, static fn (array $first, array $second): int => $second['seconds'] <=> $first['seconds']);
                try {
                    fputcsv($file, ['Sekunden', 'Status', 'Test', 'Datei', 'Zeile'], ';', '"', '');
                    foreach ($this->rows as $row) {
                        fputcsv($file, [number_format($row['seconds'], 6, '.', ''), $row['status'], $row['test'], $row['file'], $row['line']], ';', '"', '');
                    }
                } finally {
                    fclose($file);
                }
                fwrite(STDOUT, "\nAlle Testzeiten, absteigend sortiert: " . $this->csvFile . "\n");
            }
        });
    }
}
