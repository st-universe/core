<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\Ship\Lib\ShipLeaverInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\ShipStorageRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShipRemover implements ShipRemoverInterface
{
    private ShipSystemRepositoryInterface $shipSystemRepository;

    private ShipStorageRepositoryInterface $shipStorageRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    private UserRepositoryInterface $userRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private ShipLeaverInterface $shipLeaver;

    private CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend;

    private AstroEntryLibInterface $astroEntryLib;

    public function __construct(
        ShipSystemRepositoryInterface $shipSystemRepository,
        ShipStorageRepositoryInterface $shipStorageRepository,
        ShipStorageManagerInterface $shipStorageManager,
        ShipCrewRepositoryInterface $shipCrewRepository,
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        UserRepositoryInterface $userRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ShipSystemManagerInterface $shipSystemManager,
        ShipLeaverInterface $shipLeaver,
        CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend,
        AstroEntryLibInterface $astroEntryLib
    ) {
        $this->shipSystemRepository = $shipSystemRepository;
        $this->shipStorageRepository = $shipStorageRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
        $this->userRepository = $userRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->shipLeaver = $shipLeaver;
        $this->cancelColonyBlockOrDefend = $cancelColonyBlockOrDefend;
        $this->astroEntryLib = $astroEntryLib;
    }

    public function destroy(ShipInterface $ship): ?string
    {
        $msg = null;

        $this->shipSystemManager->deactivateAll($ship);

        $fleet = $ship->getFleet();

        if ($ship->isFleetLeader()) {
            $this->changeFleetLeader($ship);
        } else if ($fleet !== null) {
            $fleet->getShips()->removeElement($ship);

            $ship->setFleet(null);
            $ship->setFleetId(null);
        }

        if ($ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
        }

        //leave ship if there is crew
        if ($ship->getCrewCount() > 0) {
            $msg = $this->shipLeaver->evacuate($ship);
        }

        /**
         * this is buggy :(
         * throws ORMInvalidArgumentException
         * 
         if ($ship->getRump()->isEscapePods())
         {
             $this->remove($ship);
             return $msg;
            }
         */

        $this->leaveSomeIntactModules($ship);

        $ship->setFormerRumpId($ship->getRump()->getId());
        $ship->setRump($this->shipRumpRepository->find(ShipRumpEnum::SHIP_CATEGORY_TRUMFIELD));
        $ship->setHuell((int) ceil($ship->getMaxHuell() / 20));
        $ship->setUser($this->userRepository->find(GameEnum::USER_NOONE));
        $ship->setBuildplan(null);
        $ship->setIsBase(false);
        $ship->setShield(0);
        $ship->setEps(0);
        $ship->setAlertStateGreen();
        $ship->setInfluenceArea(null);
        $ship->setDockedTo(null);
        $ship->setName(_('Trümmer'));
        $ship->setIsDestroyed(true);
        $ship->cancelRepair();

        $this->shipSystemRepository->truncateByShip((int) $ship->getId());
        $ship->getSystems()->clear();

        $this->shipRepository->save($ship);

        foreach ($ship->getDockedShips() as $dockedShip) {
            $dockedShip->setDockedTo(null);
            $this->shipRepository->save($dockedShip);
        }

        if ($ship->isTractored()) {
            $ship->getTractoringShip()->deactivateTractorBeam();

            //TODO send pm to tractoring ship
        }

        return $msg;
    }

    private function leaveSomeIntactModules(ShipInterface $ship): void
    {
        if ($ship->isShuttle()) {
            return;
        }

        $intactModules = [];

        foreach ($ship->getSystems() as $system) {
            if (
                $system->getModule() !== null
                && $system->getStatus() == 100
            ) {
                $module = $system->getModule();

                if (!array_key_exists($module->getId(), $intactModules)) {
                    $intactModules[$module->getId()] = $module;
                }
            }
        }

        //leave 50% of all intact modules
        $leaveCount = (int) ceil(count($intactModules) / 2);
        for ($i = 1; $i <= $leaveCount; $i++) {
            $module = $intactModules[array_rand($intactModules)];
            unset($intactModules[$module->getId()]);

            $this->shipStorageManager->upperStorage(
                $ship,
                $module->getCommodity(),
                1
            );
        }
    }

    public function remove(ShipInterface $ship): void
    {
        if ($ship->isFleetLeader() && $ship->getFleet() !== null) {
            $this->changeFleetLeader($ship);
        }

        if ($ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
        }

        //both sides have to be cleared, foreign key violation
        if ($ship->isTractoring()) {
            $ship->deactivateTractorBeam();
        } else if ($ship->isTractored()) {
            $ship->getTractoringShip()->deactivateTractorBeam();
        }

        foreach ($ship->getStorage() as $item) {
            $this->shipStorageRepository->delete($item);
        }

        foreach ($ship->getDockedShips() as $dockedShip) {
            $dockedShip->setDockedTo(null);
            $this->shipRepository->save($dockedShip);
        }

        $this->shipCrewRepository->truncateByShip((int) $ship->getId());

        $this->shipRepository->delete($ship);
    }

    private function changeFleetLeader(ShipInterface $oldLeader): void
    {
        $ship = current(
            array_filter(
                $oldLeader->getFleet()->getShips()->toArray(),
                function (ShipInterface $ship) use ($oldLeader): bool {
                    return $ship !== $oldLeader;
                }
            )
        );

        if (!$ship) {
            $this->cancelColonyBlockOrDefend->work($oldLeader);
        }

        $fleet = $oldLeader->getFleet();

        $oldLeader->setFleet(null);
        $oldLeader->setIsFleetLeader(false);
        $fleet->getShips()->removeElement($oldLeader);

        $this->shipRepository->save($oldLeader);

        if (!$ship) {
            $this->fleetRepository->delete($fleet);

            return;
        }
        $fleet->setLeadShip($ship);
        $ship->setIsFleetLeader(true);

        $this->shipRepository->save($ship);
        $this->fleetRepository->save($fleet);
    }
}
