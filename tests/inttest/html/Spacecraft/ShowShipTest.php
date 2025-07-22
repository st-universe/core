<?php

declare(strict_types=1);

namespace Stu\Html\Spacecraft;

use Mockery;
use Override;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreation;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerEnum;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\StuMocks;
use Stu\TwigTestCase;

class ShowShipTest extends TwigTestCase
{
    public static function setUpBeforeClass(): void
    {
        StuMocks::get()->registerStubbedComponent(GameComponentEnum::COLONIES)
            ->registerStubbedComponent(GameComponentEnum::NAVIGATION)
            ->registerStubbedComponent(GameComponentEnum::PM)
            ->registerStubbedComponent(GameComponentEnum::RESEARCH)
            ->registerStubbedComponent(GameComponentEnum::SERVERTIME_AND_VERSION)
            ->registerStubbedComponent(GameComponentEnum::USER);
    }

    #[Override]
    public function tearDown(): void
    {
        parent::tearDown();
        PanelLayerCreation::$skippedLayers = [];
    }

    public function testHandle(): void
    {
        PanelLayerCreation::$skippedLayers[] = PanelLayerEnum::ANOMALIES->value;

        $anomalyRepositoryMock = $this->mock(AnomalyRepositoryInterface::class);
        $anomalyRepositoryMock->shouldReceive('getClosestAnomalyDistance')
            ->with(Mockery::any())
            ->andReturn(null);

        $this->getContainer()->setAdditionalService(AnomalyRepositoryInterface::class, $anomalyRepositoryMock);

        $this->renderSnapshot(
            101,
            ShowSpacecraft::class,
            ['id' => 42]
        );
    }
}
