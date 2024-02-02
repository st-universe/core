<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\StartAirfieldShip;

use request;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Module\Ship\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Repository\BuildplanHangarRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class StartAirfieldShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_START_AIRFIELD_SHIP';

    private ColonyLoaderInterface $colonyLoader;

    private CommodityRepositoryInterface $commodityRepository;

    private BuildplanHangarRepositoryInterface $buildplanHangarRepository;

    private CrewCreatorInterface $crewCreator;

    private ShipCreatorInterface $shipCreator;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private ShipTorpedoManagerInterface $shipTorpedoManager;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        CommodityRepositoryInterface $commodityRepository,
        BuildplanHangarRepositoryInterface $buildplanHangarRepository,
        CrewCreatorInterface $crewCreator,
        ShipCreatorInterface $shipCreator,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        ShipTorpedoManagerInterface $shipTorpedoManager
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->commodityRepository = $commodityRepository;
        $this->buildplanHangarRepository = $buildplanHangarRepository;
        $this->crewCreator = $crewCreator;
        $this->shipCreator = $shipCreator;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->shipTorpedoManager = $shipTorpedoManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        $rump_id = request::postInt('startrump');
        $available_rumps = $this->shipRumpRepository->getStartableByColony($colony->getId());

        if (!array_key_exists($rump_id, $available_rumps)) {
            return;
        }

        $rump = $this->shipRumpRepository->find($rump_id);

        if (
            $rump->hasSpecialAbility(ShipRumpSpecialAbilityEnum::COLONIZE) &&
            $this->shipRepository->getAmountByUserAndSpecialAbility($userId, ShipRumpSpecialAbilityEnum::COLONIZE) > 0
        ) {
            $game->addInformation(_('Es kann nur ein Schiff mit Kolonisierungsfunktion genutzt werden'));
            return;
        }
        $hangar = $this->buildplanHangarRepository->getByRump($rump->getId());

        if ($hangar->getBuildplan()->getCrew() > $colony->getCrewAssignmentAmount()) {
            $game->addInformation(_('Es ist für den Start des Schiffes nicht genügend Crew vorhanden'));
            return;
        }

        if ($colony->getEps() < $hangar->getStartEnergyCosts()) {
            $game->addInformationf(
                _('Es wird %d Energie benötigt - Vorhanden ist nur %d'),
                $hangar->getStartEnergyCosts(),
                $colony->getEps()
            );
            return;
        }

        $storage = $colony->getStorage();

        if (!$storage->containsKey($rump->getCommodityId())) {
            $game->addInformationf(
                _('Es wird %d %s benötigt'),
                1,
                $this->commodityRepository->find((int) $rump->getCommodityId())->getName()
            );
            return;
        }

        $this->colonyStorageManager->lowerStorage(
            $colony,
            $rump->getCommodity(),
            1
        );

        $shipConfigurator = $this->shipCreator->createBy(
            $userId,
            $rump_id,
            $hangar->getBuildplanId(),
            $colony
        );

        if ($rump->hasSpecialAbility(ShipRumpSpecialAbilityEnum::FULLY_LOADED_START)) {
            $shipConfigurator->maxOutSystems();
        }

        $wrapper = $shipConfigurator->finishConfiguration();
        $ship = $wrapper->get();

        $this->crewCreator->createShipCrew($ship, $colony);

        $defaultTorpedoType = $hangar->getDefaultTorpedoType();
        if ($defaultTorpedoType !== null && $storage->containsKey($defaultTorpedoType->getCommodityId())) {
            $count = $ship->getMaxTorpedos();
            if ($count > $storage[$defaultTorpedoType->getCommodityId()]->getAmount()) {
                $count = $storage[$defaultTorpedoType->getCommodityId()]->getAmount();
            }
            $this->shipTorpedoManager->changeTorpedo($wrapper, $count, $defaultTorpedoType);
            $this->shipRepository->save($ship);
            $this->colonyStorageManager->lowerStorage($colony, $defaultTorpedoType->getCommodity(), $count);
        }
        if ($hangar->getBuildplan()->getCrew() > 0) {
            $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT, true);
            $this->shipRepository->save($ship);
        }

        $colony->lowerEps($hangar->getStartEnergyCosts());

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

    public function performSessionCheck(): bool
    {
        return false;
    }
}
