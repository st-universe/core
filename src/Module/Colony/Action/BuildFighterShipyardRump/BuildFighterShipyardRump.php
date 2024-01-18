<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildFighterShipyardRump;

use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class BuildFighterShipyardRump implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BUILD_FIGHTER_SHIPYARD_RUMP';

    private ColonyLoaderInterface $colonyLoader;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        $rumpId = request::postInt('buildrump');

        $availableShipRumps = $this->shipRumpRepository->getBuildableByUserAndBuildingFunction(
            $userId,
            BuildingEnum::BUILDING_FUNCTION_FIGHTER_SHIPYARD
        );

        if (!array_key_exists($rumpId, $availableShipRumps)) {
            return;
        }

        $rump = $this->shipRumpRepository->find($rumpId);

        $wantedAmount = 1;
        $amount = 0;
        while ($amount < $wantedAmount && $this->produceShip($rump, $colony, $game)) {
            $amount++;
        }

        $this->colonyRepository->save($colony);

        if ($amount < $wantedAmount) {
            $game->addInformationf(_('Es wurden daher nur %d Stück %s-Klasse gebaut'), $amount, $rump->getName());
        } else {
            $game->addInformationf(_('%d Stück %s-Klasse wurden gebaut'), $amount, $rump->getName());
        }
    }

    private function produceShip(ShipRumpInterface $rump, ColonyInterface $colony, GameControllerInterface $game): bool
    {
        if ($rump->getEpsCost() > $colony->getEps()) {
            $game->addInformationf(
                _('Es wird %d Energie benötigt - Vorhanden ist nur %d'),
                $rump->getEpsCost(),
                $colony->getEps()
            );
            return false;
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
                return false;
            }
            if ($stor->getAmount() < $cost->getAmount()) {
                $game->addInformationf(
                    _('Es wird %d %s benötigt - Vorhanden ist nur %d'),
                    $cost->getAmount(),
                    $cost->getCommodity()->getName(),
                    $stor->getAmount()
                );
                return false;
            }
        }
        foreach ($rump->getBuildingCosts() as $cost) {
            $this->colonyStorageManager->lowerStorage($colony, $cost->getCommodity(), $cost->getAmount());
        }
        $colony->lowerEps($rump->getEpsCost());

        $this->colonyStorageManager->upperStorage($colony, $rump->getCommodity(), 1);

        return true;
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
