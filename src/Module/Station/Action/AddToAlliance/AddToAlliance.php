<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\AddToAlliance;

use request;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\StationRepositoryInterface;

final class AddToAlliance implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ADD_TO_ALLIANCE';

    public function __construct(
        private StationLoaderInterface $stationLoader,
        private StationRepositoryInterface $stationRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
        $userId = $game->getUser()->getId();

        $station = $this->stationLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        if ($game->getUser()->getAlliance() === null) {
            $game->getInfo()->addInformation('Du bist in keiner Allianz');
            return;
        }

        $rumpRoleId = $station->getRump()->getShipRumpRole()?->getId();
        if ($rumpRoleId === SpacecraftRumpRoleEnum::DEPOT_SMALL || $rumpRoleId === SpacecraftRumpRoleEnum::DEPOT_LARGE) {
            $station->setAlliance($game->getUser()->getAlliance());
            $this->stationRepository->save($station);
        }

        $game->getInfo()->addInformationf('Die Station wurde der Allianz %s hinzugefügt', $game->getUser()->getAlliance()->getName());
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
