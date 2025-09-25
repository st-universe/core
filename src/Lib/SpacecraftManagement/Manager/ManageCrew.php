<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Manager;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Module\Spacecraft\Lib\Crew\SpacecraftLeaverInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftStartupInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\Spacecraft;

class ManageCrew implements ManagerInterface
{
    public function __construct(
        private readonly SpacecraftCrewCalculatorInterface $shipCrewCalculator,
        private readonly TroopTransferUtilityInterface $troopTransferUtility,
        private readonly SpacecraftShutdownInterface $spacecraftShutdown,
        private readonly SpacecraftLeaverInterface $spacecraftLeaver,
        private readonly ActivatorDeactivatorHelperInterface $helper,
        private readonly SpacecraftStartupInterface $spacecraftStartup
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
            && $ship->getUser()->getId() === $user->getId()
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
        SpacecraftBuildplan $buildplan,
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
        SpacecraftBuildplan $buildplan,
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
                $actualcrew = $ship->getCrewCount();
                $maxcrew = $this->shipCrewCalculator->getMaxCrewCountByRump($ship->getRump());

                if (
                    $actualcrew + $additionalCrew > $maxcrew
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

            $this->spacecraftStartup->startup($wrapper);
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

        if ($ship->getBuildplan() != null) {
            $mincrew = $ship->getBuildplan()->getCrew();
            $actualcrew = $ship->getCrewCount();
            $maxcrew = $this->shipCrewCalculator->getMaxCrewCountByRump($ship->getRump());

            if (
                $actualcrew - $removedCrew >= $mincrew && $actualcrew - $removedCrew < $maxcrew &&
                $ship->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TROOP_QUARTERS)
                && ($removedCrew > 0 && $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TROOP_QUARTERS)->getMode() === SpacecraftSystemModeEnum::MODE_ON
                )
            ) {
                $informations = new InformationWrapper();
                $this->helper->deactivate($wrapper, SpacecraftSystemTypeEnum::TROOP_QUARTERS, $informations);
                $msg = array_merge($msg, $informations->getInformations());
            }
        }


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

    private function dumpForeignCrew(Spacecraft $spacecraft): void
    {
        foreach ($spacecraft->getCrewAssignments() as $shipCrew) {
            if ($shipCrew->getCrew()->getUser()->getId() !== $spacecraft->getUser()->getId()) {
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