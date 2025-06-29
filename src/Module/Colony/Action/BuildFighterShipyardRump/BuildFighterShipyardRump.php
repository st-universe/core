<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildFighterShipyardRump;

use Override;
use request;
use RuntimeException;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

final class BuildFighterShipyardRump implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_BUILD_FIGHTER_SHIPYARD_RUMP';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private StorageManagerInterface $storageManager,
        private ColonyRepositoryInterface $colonyRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        $rumpId = request::postInt('buildrump');

        $availableShipRumps = $this->spacecraftRumpRepository->getBuildableByUserAndBuildingFunction(
            $userId,
            BuildingFunctionEnum::FIGHTER_SHIPYARD
        );

        if (!array_key_exists($rumpId, $availableShipRumps)) {
            return;
        }

        $rump = $this->spacecraftRumpRepository->find($rumpId);
        if ($rump === null) {
            throw new SanityCheckException(sprintf('rumpId %d does not exist', $rumpId));
        }

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

    private function produceShip(SpacecraftRump $rump, Colony $colony, GameControllerInterface $game): bool
    {
        $changeable = $colony->getChangeable();

        if ($rump->getEpsCost() > $changeable->getEps()) {
            $game->addInformationf(
                _('Es wird %d Energie benötigt - Vorhanden ist nur %d'),
                $rump->getEpsCost(),
                $changeable->getEps()
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
            $this->storageManager->lowerStorage($colony, $cost->getCommodity(), $cost->getAmount());
        }
        $changeable->lowerEps($rump->getEpsCost());

        $commodity = $rump->getCommodity();
        if ($commodity === null) {
            throw new RuntimeException(sprintf('rumpId %d does not have commodity', $rump->getId()));
        }

        $this->storageManager->upperStorage($colony, $commodity, 1);

        return true;
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
