<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Colony\ColonyList;

use Doctrine\Common\Collections\Collection;
use Mockery\MockInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Module\Colony\Lib\CommodityConsumptionInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuApiV1TestCase;

class GetColonyListTest extends StuApiV1TestCase
{
    /**
     * @var null|MockInterface|CommodityConsumptionInterface
     */
    private $commodityConsumption;

    /**
     * @var null|MockInterface|SessionInterface
     */
    private $session;

    public function setUp(): void
    {
        $this->commodityConsumption = $this->mock(CommodityConsumptionInterface::class);
        $this->session = $this->mock(SessionInterface::class);

        $this->setUpApiHandler(
            new GetColonyList(
                $this->commodityConsumption,
                $this->session
            )
        );
    }

    public function testActionReturnsList(): void
    {
        $colony = $this->mock(ColonyInterface::class);
        $user = $this->mock(UserInterface::class);

        $colonyId = 666;
        $colonyName = 'some-colony-name';
        $planetName = 'some-planet-name';
        $systemName = 'some-system-name';
        $systemType = 42;
        $systemCx = 11;
        $systemCy = 22;
        $sx = 33;
        $sy = 44;
        $worker = 555;
        $workless = 666;
        $freeHousing = 777;
        $maximumHousing = 888;
        $currentEnergy = 1111;
        $maximumEnergy = 2222;
        $energyProduction = 3333;
        $storageAmount = 11111;
        $maximumStorage = 22222;
        $consumptionCommodityId = 999;
        $consumptionProduction = -666;
        $consumptionTurnsLeft = 4;

        $this->session->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $colony->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($colonyId);
        $colony->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($colonyName);
        $colony->shouldReceive('getPlanetName')
            ->withNoArgs()
            ->once()
            ->andReturn($planetName);
        $colony->shouldReceive('getSystem->getName')
            ->withNoArgs()
            ->once()
            ->andReturn($systemName);
        $colony->shouldReceive('getSystem->getSystemType->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($systemType);
        $colony->shouldReceive('getSystem->getCx')
            ->withNoArgs()
            ->once()
            ->andReturn($systemCx);
        $colony->shouldReceive('getSystem->getCy')
            ->withNoArgs()
            ->once()
            ->andReturn($systemCy);
        $colony->shouldReceive('getSx')
            ->withNoArgs()
            ->once()
            ->andReturn($sx);
        $colony->shouldReceive('getSy')
            ->withNoArgs()
            ->once()
            ->andReturn($sy);
        $colony->shouldReceive('getWorkers')
            ->withNoArgs()
            ->once()
            ->andReturn($worker);
        $colony->shouldReceive('getWorkless')
            ->withNoArgs()
            ->once()
            ->andReturn($workless);
        $colony->shouldReceive('getFreeHousing')
            ->withNoArgs()
            ->once()
            ->andReturn($freeHousing);
        $colony->shouldReceive('getMaxBev')
            ->withNoArgs()
            ->once()
            ->andReturn($maximumHousing);
        $colony->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn($currentEnergy);
        $colony->shouldReceive('getMaxEps')
            ->withNoArgs()
            ->once()
            ->andReturn($maximumEnergy);
        $colony->shouldReceive('getEpsProduction')
            ->withNoArgs()
            ->once()
            ->andReturn($energyProduction);
        $colony->shouldReceive('getStorageSum')
            ->withNoArgs()
            ->once()
            ->andReturn($storageAmount);
        $colony->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($maximumStorage);

        $this->commodityConsumption->shouldReceive('getConsumption')
            ->with($colony)
            ->once()
            ->andReturn([
                $consumptionCommodityId => [
                    'production' => $consumptionProduction,
                    'turnsleft' => $consumptionTurnsLeft
                ]
            ]);

        $colonyList = $this->mock(Collection::class);
        $user->shouldReceive('getColonies')
            ->withNoArgs()
            ->once()
            ->andReturn($colonyList);

        $colonyList->shouldReceive('toArray')
            ->withNoArgs()
            ->once()
            ->andReturn([$colony]);

        $this->response->shouldReceive('withData')
            ->with([[
                'id' => $colonyId,
                'name' => $colonyName,
                'location' => [
                    'planetName' => $planetName,
                    'systemName' => $systemName,
                    'systemType' => $systemType,
                    'systemCx' => $systemCx,
                    'systemCy' => $systemCy,
                    'sx' => $sx,
                    'sy' => $sy
                ],
                'population' => [
                    'working' => $worker,
                    'workless' => $workless,
                    'freeHousing' => $freeHousing,
                    'maximumHousing' => $maximumHousing
                ],
                'energy' => [
                    'currentAmount' => $currentEnergy,
                    'maximumAmount' => $maximumEnergy,
                    'production' => $energyProduction
                ],
                'storage' => [
                    'currentAmount' => $storageAmount,
                    'maximumAmount' => $maximumStorage,
                    'commodityConsumption' => [
                        [
                            'commodityId' => $consumptionCommodityId,
                            'production' => $consumptionProduction,
                            'turnsLeft' => $consumptionTurnsLeft,
                        ]
                    ]
                ]
            ]])
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }
}
