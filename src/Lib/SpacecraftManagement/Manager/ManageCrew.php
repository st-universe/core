<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Manager;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaverInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\SpacecraftInterface;

class ManageCrew implements ManagerInterface
{
    public function __construct(
        private SpacecraftCrewCalculatorInterface $shipCrewCalculator,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private TroopTransferUtilityInterface $troopTransferUtility,
        private SpacecraftShutdownInterface $spacecraftShutdown,
        private SpacecraftLeaverInterface $spacecraftLeaver,
        private ActivatorDeactivatorHelperInterface $helper
    ) {}

    #[Override]
    public function manage(SpacecraftWrapperInterface $wrapper, array $values, ManagerProviderInterface $managerProvider): array
    {
        $msg = [];
        $informations = new InformationWrapper();

        $newCrewCountArray = $values['crew'] ?? null;
        if ($newCrewCountArray === null) {
            throw new RuntimeException('value array not existent');
        }

        $ship = $wrapper->get();
        $user = $managerProvider->getUser();
        $buildplan = $ship->getBuildplan();

        if (
            isset($newCrewCountArray[$ship->getId()])
            && $wrapper->canMan()
            && $buildplan !== null
            && $ship->getUser() === $user
        ) {
            $newCrewCount = (int)$newCrewCountArray[$ship->getId()];
            if ($ship->getCrewCount() !== $newCrewCount) {
                $this->setNewCrew($newCrewCount, $wrapper, $buildplan, $managerProvider, $msg, $informations);
            }
        }

        return array_merge($msg, $informations->getInformations());
    }

    /**
     * @param array<string> $msg
     */
    private function setNewCrew(
        int $newCrewCount,
        SpacecraftWrapperInterface $wrapper,
        SpacecraftBuildplanInterface $buildplan,
        ManagerProviderInterface $managerProvider,
        array &$msg,
        InformationWrapper $informations
    ): void {
        $ship = $wrapper->get();

        if ($newCrewCount > $ship->getCrewCount()) {
            $this->increaseCrew($newCrewCount, $wrapper, $buildplan, $managerProvider, $msg, $informations);
        } else {
            $this->descreaseCrew($newCrewCount, $wrapper, $managerProvider, $msg);
        }
    }

    /**
     * @param array<string> $msg
     */
    private function increaseCrew(
        int $newCrewCount,
        SpacecraftWrapperInterface $wrapper,
        SpacecraftBuildplanInterface $buildplan,
        ManagerProviderInterface $managerProvider,
        array &$msg,
        InformationWrapper $informations
    ): void {
        $ship = $wrapper->get();

        if ($managerProvider->getFreeCrewAmount() == 0) {
            $msg[] = sprintf(
                _('%s: Keine Crew auf der %s vorhanden'),
                $ship->getName(),
                $managerProvider->getName(),
                $buildplan->getCrew()
            );
        } else {
            $additionalCrew = min(
                $newCrewCount - $ship->getCrewCount(),
                $managerProvider->getFreeCrewAmount()
            );

            if ($ship->getBuildplan() != null) {
                $mincrew = $ship->getBuildplan()->getCrew();
                $actualcrew = $ship->getCrewCount();
                $maxcrew = $this->shipCrewCalculator->getMaxCrewCountByRump($ship->getRump());

                if (
                    $actualcrew >= $mincrew
                    && $actualcrew + $additionalCrew > $maxcrew
                    && ($ship->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TROOP_QUARTERS) && ($additionalCrew > 0
                        && $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TROOP_QUARTERS)->getMode() === SpacecraftSystemModeEnum::MODE_OFF
                        && !$this->helper->activate($wrapper, SpacecraftSystemTypeEnum::TROOP_QUARTERS, $informations)))
                ) {

                    $additionalCrew  = 0;
                }
            }

            $managerProvider->addCrewAssignment($ship, $additionalCrew);
            $msg[] = sprintf(
                _('%s: %d Crewman wurde(n) hochgebeamt'),
                $ship->getName(),
                $additionalCrew
            );

            if ($ship->hasSpacecraftSystem(SpacecraftSystemTypeEnum::LIFE_SUPPORT)) {
                $this->spacecraftSystemManager->activate($wrapper, SpacecraftSystemTypeEnum::LIFE_SUPPORT, true);
            }
        }
    }

    /**
     * @param array<string> $msg
     */
    private function descreaseCrew(
        int $newCrewCount,
        SpacecraftWrapperInterface $wrapper,
        ManagerProviderInterface $managerProvider,
        array &$msg
    ): void {
        $ship = $wrapper->get();
        $user = $managerProvider->getUser();

        if ($managerProvider->getFreeCrewStorage() == 0) {
            $msg[] = sprintf(
                _('%s: Kein Platz für die Crew auf der %s'),
                $ship->getName(),
                $managerProvider->getName()
            );
            return;
        }

        $ownCrewCount = $this->troopTransferUtility->ownCrewOnTarget($user, $ship);

        $removedCrew = min(
            $ownCrewCount,
            $ship->getCrewCount() - $newCrewCount,
            $managerProvider->getFreeCrewStorage()
        );

        $this->dumpForeignCrew($ship);

        $managerProvider->addCrewAssignments(array_slice(
            $ship->getCrewAssignments()->toArray(),
            0,
            $removedCrew
        ));

        $msg[] = sprintf(
            _('%s: %d Crewman wurde(n) runtergebeamt'),
            $ship->getName(),
            $removedCrew
        );

        if ($removedCrew === $ownCrewCount) {
            $this->spacecraftShutdown->shutdown($wrapper);
        }
    }

    private function dumpForeignCrew(SpacecraftInterface $spacecraft): void
    {
        foreach ($spacecraft->getCrewAssignments() as $shipCrew) {
            if ($shipCrew->getCrew()->getUser() !== $spacecraft->getUser()) {
                $this->spacecraftLeaver->dumpCrewman(
                    $shipCrew,
                    sprintf(
                        'Die Dienste von Crewman %s werden nicht mehr auf der Station %s von Spieler %s benötigt.',
                        $shipCrew->getCrew()->getName(),
                        $spacecraft->getName(),
                        $spacecraft->getUser()->getName(),
                    )
                );
            }
        }
    }
}
