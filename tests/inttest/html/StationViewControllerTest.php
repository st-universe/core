<?php

declare(strict_types=1);

namespace Stu\Html;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use Stu\Config\Init;
use Stu\Module\Control\ViewControllerInterface;
use Stu\StubGameComponentsTrait;
use Stu\TwigTestCase;

class StationViewControllerTest extends TwigTestCase
{
    use StubGameComponentsTrait;

    private const array CURRENTLY_UNSUPPORTED_KEYS = [
        'STATION_VIEWS-SHOW_SENSOR_SCAN',               // render not possible if template is not set
        'STATION_VIEWS-SHOW_SHIP_REPAIR',               // render not possible if template is not set
        'STATION_VIEWS-SHOW_SHUTTLE_MANAGEMENT',        // Parameter "entity" not found
        'STATION_VIEWS-SHOW_STATION_COSTS',             // needs construction
        'STATION_VIEWS-SHOW_SYSTEM_SENSOR_SCAN',        // render not possible if template is not set
    ];

    private string $snapshotKey = '';

    #[\Override]
    protected function getSnapshotId(): string
    {
        return new ReflectionClass($this)->getShortName() . '--' .
            $this->snapshotKey;
    }

    public static function getAllViewControllerDataProvider(): array
    {
        $definedImplementations =  Init::getContainer()
            ->getDefinedImplementationsOf(ViewControllerInterface::class, true);

        return $definedImplementations
            ->map(fn (ViewControllerInterface $viewController): array => [$definedImplementations->indexOf($viewController)])
            ->filter(fn (array $array): bool => !str_ends_with($array[0], '-DEFAULT_VIEW') && str_starts_with($array[0], 'STATION_VIEWS'))
            ->filter(fn (array $array): bool => !in_array($array[0], self::CURRENTLY_UNSUPPORTED_KEYS))
            ->toArray();
    }

    #[DataProvider('getAllViewControllerDataProvider')]
    public function testHandle(string $key): void
    {
        $this->stubGameComponents();

        $this->snapshotKey = $key;

        $this->renderSnapshot(
            101,
            Init::getContainer()
                ->getDefinedImplementationsOf(ViewControllerInterface::class, true)->get($key),
            $this->getGeneralRequestVariables()
        );
    }

    private function getGeneralRequestVariables(): array
    {
        return [
            'id' => 43,
            'planid' => 689,
            'userid' => 101,
            'factionid' => 1,
            'layerid' => 2,
            'section' => 1,
            'systemid' => 252,
            'x' => 5,
            'y' => 5,
        ];
    }
}
