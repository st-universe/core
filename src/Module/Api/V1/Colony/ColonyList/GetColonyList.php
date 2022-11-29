<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Colony\ColonyList;

use Psr\Http\Message\ServerRequestInterface;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Module\Colony\Lib\CommodityConsumptionInterface;
use Stu\Orm\Entity\ColonyInterface;

final class GetColonyList extends Action
{
    private CommodityConsumptionInterface $commodityConsumption;

    private SessionInterface $session;

    public function __construct(
        CommodityConsumptionInterface $commodityConsumption,
        SessionInterface $session
    ) {
        $this->commodityConsumption = $commodityConsumption;
        $this->session = $session;
    }

    protected function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface {
        return $response->withData(
            array_map(
                function (ColonyInterface $colony): array {
                    $consumption = [];

                    foreach ($this->commodityConsumption->getConsumption($colony) as $commodityId => $item) {
                        $consumption[] = [
                            'commodityId' => $commodityId,
                            'production' => $item['production'],
                            'turnsLeft' => $item['turnsleft'],
                        ];
                    }

                    return [
                        'id' => $colony->getId(),
                        'name' => $colony->getName(),
                        'location' => [
                            'planetName' => $colony->getPlanetName(),
                            'systemName' => $colony->getSystem()->getName(),
                            'systemType' => $colony->getSystem()->getSystemType()->getId(),
                            'systemCx' => $colony->getSystem()->getCx(),
                            'systemCy' => $colony->getSystem()->getCy(),
                            'sx' => $colony->getSx(),
                            'sy' => $colony->getSy()
                        ],
                        'population' => [
                            'working' => $colony->getWorkers(),
                            'workless' => $colony->getWorkless(),
                            'freeHousing' => $colony->getFreeHousing(),
                            'maximumHousing' => $colony->getMaxBev()
                        ],
                        'energy' => [
                            'currentAmount' => $colony->getEps(),
                            'maximumAmount' => $colony->getMaxEps(),
                            'production' => $colony->getEpsProduction()
                        ],
                        'storage' => [
                            'currentAmount' => $colony->getStorageSum(),
                            'maximumAmount' => $colony->getMaxStorage(),
                            'commodityConsumption' => $consumption
                        ]
                    ];
                },
                $this->session->getUser()->getColonies()->toArray()
            )
        );
    }
}
