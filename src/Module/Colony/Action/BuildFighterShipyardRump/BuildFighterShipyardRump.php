<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildFighterShipyardRump;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class BuildFighterShipyardRump implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BUILD_FIGHTER_SHIPYARD_RUMP';

    private $colonyLoader;

    private $shipRumpRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShipRumpRepositoryInterface $shipRumpRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->shipRumpRepository = $shipRumpRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $rumpId = (int) request::postInt('buildrump');

        $availableShipRumps = $this->shipRumpRepository->getBuildableByUserAndBuildingFunction(
            $userId,
            BUILDING_FUNCTION_FIGHTER_SHIPYARD
        );

        if (!array_key_exists($rumpId, $availableShipRumps)) {
            return;
        }

        $rump = $this->shipRumpRepository->find($rumpId);

        if ($rump->getEpsCost() > $colony->getEps()) {
            $game->addInformationf(
                _('Es wird %d Energie benötigt - Vorhanden ist nur %d'),
                $rump->getEpsCost(),
                $colony->getEps()
            );
            return;
        }
        $storage = $colony->getStorage();
        foreach ($rump->getBuildingCosts() as $cost) {
            $stor = $storage[$cost->getCommodityId()] ?? null;

            if ($stor === null) {
                $game->addInformationf(
                    _('Es wird %d %s benötigt'),
                    $cost->getAmount(),
                    $cost->getCommodity()->getName()
                );
                return;
            }
            if ($stor->getAmount() < $cost->getAmount()) {
                $game->addInformationf(
                    _('Es wird %d %s benötigt - Vorhanden ist nur %d'),
                    $cost->getAmount(),
                    $cost->getCommodity()->getName(),
                    $stor->getAmount()
                );
                return;
            }
        }
        foreach ($rump->getBuildingCosts() as $cost) {
            $colony->lowerStorage($cost->getCommodityId(), $cost->getAmount());
        }
        $colony->lowerEps($rump->getEpsCost());
        $colony->upperStorage($rump->getGoodId(), 1);
        $colony->save();
        $game->addInformationf(_('%s-Klasse wurde gebaut'), $rump->getName());
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
