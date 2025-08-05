<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\StartAirfieldShip;

use Doctrine\Common\Collections\Collection;
use Override;
use request;
use RuntimeException;
use Stu\Component\Database\AchievementManagerInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\BuildplanHangar;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Repository\BuildplanHangarRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class StartAirfieldShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_START_AIRFIELD_SHIP';

    // start with fully loaded warpcore/eps from airfields
    private const int FULLY_LOADED_START = 2;

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
        private ShipTorpedoManagerInterface $shipTorpedoManager,
        private readonly AchievementManagerInterface $achievementManager
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
            $rump->hasSpecialAbility(SpacecraftRump::SPECIAL_ABILITY_COLONIZE) &&
            $this->spacecraftRepository->getAmountByUserAndSpecialAbility($userId, SpacecraftRump::SPECIAL_ABILITY_COLONIZE) > 0
        ) {
            $game->getInfo()->addInformation(_('Es kann nur ein Schiff mit Kolonisierungsfunktion genutzt werden'));
            return;
        }

        $hangar = $this->buildplanHangarRepository->getByRump($rump->getId());
        if ($hangar === null) {
            throw new SanityCheckException();
        }

        if ($hangar->getBuildplan()->getCrew() > $colony->getCrewAssignmentAmount()) {
            $game->getInfo()->addInformation(_('Es ist für den Start des Schiffes nicht genügend Crew vorhanden'));
            return;
        }

        $changeable = $colony->getChangeable();

        if ($changeable->getEps() < $hangar->getStartEnergyCosts()) {
            $game->getInfo()->addInformationf(
                _('Es wird %d Energie benötigt - Vorhanden ist nur %d'),
                $hangar->getStartEnergyCosts(),
                $changeable->getEps()
            );
            return;
        }

        $storages = $colony->getStorage();
        if (!$storages->containsKey($commodity->getId())) {
            $game->getInfo()->addInformationf(
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

        if ($rump->hasSpecialAbility(self::FULLY_LOADED_START)) {
            $shipConfigurator->maxOutSystems();
        }

        $wrapper = $shipConfigurator->finishConfiguration();
        $ship = $wrapper->get();

        $this->crewCreator->createCrewAssignment($ship, $colony);
        $this->loadTorpedos($hangar, $storages, $wrapper, $colony);

        if ($hangar->getBuildplan()->getCrew() > 0) {
            $this->spacecraftSystemManager->activate($wrapper, SpacecraftSystemTypeEnum::LIFE_SUPPORT, true);
            $this->shipRepository->save($ship);
        }

        $changeable->lowerEps($hangar->getStartEnergyCosts());

        $this->colonyRepository->save($colony);

        $databaseEntry = $colony->getSystem()->getDatabaseEntry();
        $this->achievementManager->checkDatabaseItem($databaseEntry?->getId(), $user);
        $databaseEntry = $colony->getSystem()->getSystemType()->getDatabaseEntry();
        $this->achievementManager->checkDatabaseItem($databaseEntry?->getId(), $user);
        $this->achievementManager->checkDatabaseItem($rump->getDatabaseId(), $user);
        $game->getInfo()->addInformation(_('Das Schiff wurde gestartet'));
    }

    /**
     * @param Collection<int, Storage> $storages
     */
    private function loadTorpedos(BuildplanHangar $hangar, Collection $storages, ShipWrapperInterface $wrapper, Colony $colony): void
    {
        $defaultTorpedoType = $hangar->getDefaultTorpedoType();
        if ($defaultTorpedoType === null) {
            return;
        }

        $storage = $storages->get($defaultTorpedoType->getCommodityId());
        if ($storage === null) {
            return;
        }

        $ship = $wrapper->get();
        $count = $ship->getMaxTorpedos();
        if ($count > $storage->getAmount()) {
            $count = $storage->getAmount();
        }
        $this->shipTorpedoManager->changeTorpedo($wrapper, $count, $defaultTorpedoType);
        $this->shipRepository->save($ship);
        $this->storageManager->lowerStorage($colony, $defaultTorpedoType->getCommodity(), $count);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
