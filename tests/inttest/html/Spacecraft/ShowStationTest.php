<?php

declare(strict_types=1);

namespace Stu\Html\Spacecraft;

use Mockery;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\StuMocks;
use Stu\TwigTestCase;

class ShowStationTest extends TwigTestCase
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

    public function testHandle(): void
    {
        $anomalyRepositoryMock = $this->mock(AnomalyRepositoryInterface::class);
        $anomalyRepositoryMock->shouldReceive('getClosestAnomalyDistance')
            ->with(Mockery::any())
            ->andReturn(null);

        $this->getContainer()->setAdditionalService(AnomalyRepositoryInterface::class, $anomalyRepositoryMock);

        $this->renderSnapshot(
            101,
            ShowSpacecraft::class,
            ['id' => 43]
        );
    }
}
