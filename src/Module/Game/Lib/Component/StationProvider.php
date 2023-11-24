<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\Component;

use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class StationProvider implements ViewComponentProviderInterface
{
    private ShipRepositoryInterface $shipRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $bases = $this->shipRepository->getByUserAndFleetAndType($userId, null, SpacecraftTypeEnum::SPACECRAFT_TYPE_STATION);
        $uplinkBases = $this->shipRepository->getByUplink($userId);

        $game->setTemplateVar(
            'BASES',
            $this->shipWrapperFactory->wrapShips(array_merge($bases, $uplinkBases))
        );
    }
}
