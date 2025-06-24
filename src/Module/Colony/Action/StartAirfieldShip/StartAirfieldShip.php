<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\StartAirfieldShip;

use Override;
use request;
use RuntimeException;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Spacecraft\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Repository\BuildplanHangarRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class StartAirfieldShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_START_AIRFIELD_SHIP';

    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private ColonyLoaderInterface $colonyLoader,
        private BuildplanHangarRepositoryInterface $buildplanHangarRepository,
        private CrewCreatorInterface $crewCreator,
        private ShipCreatorInterface $shipCreator,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private StorageManagerInterface $storageManager,
        private ColonyRepositoryInterface $colonyRepository,
        private ShipRepositoryInterface $shipRepository,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private ShipTorpedoManagerInterface $shipTorpedoManager
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        $rumpid = request::postInt('startrump');
        $available_rumps = $this->spacecraftRumpRepository->getStartableByColony($colony->getId());

        if (!array_key_exists($rumpid, $available_rumps)) {
            return;
        }

        $rump = $this->spacecraftRumpRepository->find($rumpid);
        if ($rump === null) {
            throw new RuntimeException(sprintf('rumpId %d does not exist', $rumpid));
        }

        $commodity = $rump->getCommodity();
        if ($commodity === null) {
            throw new RuntimeException(sprintf('rumpId %d does not have commodity', $rumpid));
        }

        if (
            $rump->hasSpecialAbility(ShipRumpSpecialAbilityEnum::COLONIZE) &&
            $this->spacecraftRepository->getAmountByUserAndSpecialAbility($userId, ShipRumpSpecialAbilityEnum::COLONIZE) > 0
        ) {
            $game->addInformation(_('Es kann nur ein Schiff mit Kolonisierungsfunktion genutzt werden'));
            return;
        }
        $hangar = $this->buildplanHangarRepository->getByRump($rump->getId());

        if ($hangar->getBuildplan()->getCrew() > $colony->getCrewAssignmentAmount()) {
            $game->addInformation(_('Es ist für den Start des Schiffes nicht genügend Crew vorhanden'));
            return;
        }

        $changeable = $colony->getChangeable();

        if ($changeable->getEps() < $hangar->getStartEnergyCosts()) {
            $game->addInformationf(
                _('Es wird %d Energie benötigt - Vorhanden ist nur %d'),
                $hangar->getStartEnergyCosts(),
                $changeable->getEps()
            );
            return;
        }

        $storage = $colony->getStorage();
        if (!$storage->containsKey($commodity->getId())) {
            $game->addInformationf(
                _('Es wird %d %s benötigt'),
                1,
                $commodity->getName()
            );
            return;
        }

        $this->storageManager->lowerStorage(
            $colony,
            $commodity,
            1
        );

        $shipConfigurator = $this->shipCreator->createBy(
            $userId,
            $rumpid,
            $hangar->getBuildplanId()
        )->setLocation($colony->getStarsystemMap());

        if ($rump->hasSpecialAbility(ShipRumpSpecialAbilityEnum::FULLY_LOADED_START)) {
            $shipConfigurator->maxOutSystems();
        }

        $wrapper = $shipConfigurator->finishConfiguration();
        $ship = $wrapper->get();

        $this->crewCreator->createCrewAssignment($ship, $colony);

        $defaultTorpedoType = $hangar->getDefaultTorpedoType();
        if ($defaultTorpedoType !== null && $storage->containsKey($defaultTorpedoType->getCommodityId())) {
            $count = $ship->getMaxTorpedos();
            if ($count > $storage[$defaultTorpedoType->getCommodityId()]->getAmount()) {
                $count = $storage[$defaultTorpedoType->getCommodityId()]->getAmount();
            }
            $this->shipTorpedoManager->changeTorpedo($wrapper, $count, $defaultTorpedoType);
            $this->shipRepository->save($ship);
            $this->storageManager->lowerStorage($colony, $defaultTorpedoType->getCommodity(), $count);
        }
        if ($hangar->getBuildplan()->getCrew() > 0) {
            $this->spacecraftSystemManager->activate($wrapper, SpacecraftSystemTypeEnum::LIFE_SUPPORT, true);
            $this->shipRepository->save($ship);
        }

        $changeable->lowerEps($hangar->getStartEnergyCosts());

        $this->colonyRepository->save($colony);

        $databaseEntry = $colony->getSystem()->getDatabaseEntry();
        if ($databaseEntry !== null) {
            $game->checkDatabaseItem($databaseEntry->getId());
        }
        $databaseEntry = $colony->getSystem()->getSystemType()->getDatabaseEntry();
        if ($databaseEntry !== null) {
            $game->checkDatabaseItem($databaseEntry->getId());
        }
        if ($rump->getDatabaseId()) {
            $game->checkDatabaseItem($rump->getDatabaseId());
        }
        $game->addInformation(_('Das Schiff wurde gestartet'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
