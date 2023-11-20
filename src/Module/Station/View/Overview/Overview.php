<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\Overview;

use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STATION_LIST';

    private ShipRepositoryInterface $shipRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $bases = $this->shipRepository->getByUserAndFleetAndType($userId, null, SpacecraftTypeEnum::SPACECRAFT_TYPE_STATION);
        $uplinkBases = $this->shipRepository->getByUplink($userId);

        $game->appendNavigationPart(
            'station.php',
            _('Stationen')
        );
        $game->setPageTitle(_('/ Stationen'));
        $game->setTemplateFile('html/stationlist.twig');

        $game->setTemplateVar(
            'BASES',
            $this->shipWrapperFactory->wrapShips(array_merge($bases, $uplinkBases))
        );
    }
}
