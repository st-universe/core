<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Stu\Component\Faction\FactionEnum;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\ColonyClass;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class ColonyPopulationCalculatorTest extends StuTestCase
{
    public function testGetGrowthAppliesKlingonBonus(): void
    {
        $subject = $this->createSubject(
            FactionEnum::FACTION_KLINGON,
            100,
            1000
        );

        static::assertSame(5, $subject->getGrowth());
    }

    public function testGetGrowthReturnsZeroForKlingonGrowthBelowOne(): void
    {
        $subject = $this->createSubject(
            FactionEnum::FACTION_KLINGON,
            100,
            200
        );

        static::assertSame(0, $subject->getGrowth());
    }

    private function createSubject(FactionEnum $faction, int $population, int $maxPopulation): ColonyPopulationCalculator
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getFactionId')
            ->withNoArgs()
            ->andReturn($faction->value);

        $colony = new Colony();
        $changeable = (new ColonyChangeable($colony))
            ->setWorkers($population)
            ->setMaxBev($maxPopulation);
        $colonyClass = (new ColonyClass())->setBevGrowthRate(1);
        $colony->setUser($user)
            ->setChangeable($changeable)
            ->setColonyClass($colonyClass);

        return new ColonyPopulationCalculator(
            $colony,
            [
                CommodityTypeConstants::COMMODITY_EFFECT_LIFE_STANDARD => new ColonyProduction(
                    new Commodity(),
                    (int) ($population / 2),
                    null
                )
            ]
        );
    }
}
