<?php

declare(strict_types=1);

namespace Stu\Html\Spacecraft;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\View\ShowSystemSettings\ShowSystemSettings;
use Stu\TwigTestCase;

class ShowSystemSettingsTest extends TwigTestCase
{
    #[Override]
    protected function getViewControllerClass(): string
    {
        return ShowSystemSettings::class;
    }

    public static function getSystemTypesProvider(): array
    {
        return [
            [SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM, 1022],
            [SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR, 1021],
            [SpacecraftSystemTypeEnum::THOLIAN_WEB, 1021]
        ];
    }

    #[DataProvider('getSystemTypesProvider')]
    public function testHandle(SpacecraftSystemTypeEnum $type, int $id): void
    {
        $this->renderSnapshot(
            102,
            [
                'id' => $id,
                'system' => $type->name
            ]
        );
    }
}
