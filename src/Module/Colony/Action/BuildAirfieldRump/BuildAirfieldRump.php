<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildAirfieldRump;

use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Module\Colony\Lib\ColonyStorageManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class BuildAirfieldRump implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BUILD_AIRFIELD_RUMP';

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

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $rumpId = (int) request::postInt('buildrump');

        $availableShipRumps = $this->shipRumpRepository->getBuildableByUserAndBuildingFunction(
            $userId,
            BuildingEnum::BUILDING_FUNCTION_AIRFIELD
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
            $this->colonyStorageManager->lowerStorage($colony, $cost->getCommodity(), $cost->getAmount());
        }
        $colony->lowerEps($rump->getEpsCost());

        $this->colonyStorageManager->upperStorage($colony, $rump->getCommodity(), 1);

        $this->colonyRepository->save($colony);

        $game->addInformationf(_('%s-Klasse wurde gebaut'), $rump->getName());
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
