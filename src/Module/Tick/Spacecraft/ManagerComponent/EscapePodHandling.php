<?php

namespace Stu\Module\Tick\Spacecraft\ManagerComponent;

use Override;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class EscapePodHandling implements ManagerComponentInterface
{
    public function __construct(private PrivateMessageSenderInterface $privateMessageSender, private SpacecraftRemoverInterface $spacecraftRemover, private ShipRepositoryInterface $shipRepository, private CrewAssignmentRepositoryInterface $shipCrewRepository, private ColonyLibFactoryInterface $colonyLibFactory) {}

    #[Override]
    public function work(): void
    {
        $escapedToColonies = [];

        foreach ($this->shipRepository->getEscapePods() as $escapePod) {
            if ($escapePod->getCrewCount() === 0) {
                $this->spacecraftRemover->remove($escapePod);
            }

            if ($escapePod->getStarsystemMap() !== null) {
                $colony = $escapePod->getStarsystemMap()->getColony();

                if ($colony !== null) {
                    $count = $this->transferOwnCrewToColony($escapePod, $colony);

                    if ($count > 0) {
                        if (array_key_exists($colony->getId(), $escapedToColonies)) {
                            $oldCount = $escapedToColonies[$colony->getId()][1];

                            $escapedToColonies[$colony->getId()][1] = $oldCount +  $count;
                        } else {
                            $escapedToColonies[$colony->getId()] = [$colony, $count];
                        }
                    }
                }
            }
        }

        foreach ($escapedToColonies as [$colony, $count]) {
            $msg = sprintf(_('%d deiner Crewman sind aus Fluchtkapseln auf deiner Kolonie %s gelandet'), $count, $colony->getName());
            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $colony->getUser()->getId(),
                $msg,
                PrivateMessageFolderTypeEnum::SPECIAL_COLONY
            );
        }
    }

    private function transferOwnCrewToColony(Ship $escapePod, Colony $colony): int
    {
        $count = 0;

        foreach ($escapePod->getCrewAssignments() as $crewAssignment) {
            if ($crewAssignment->getUser() !== $colony->getUser()) {
                continue;
            }

            $freeAssignmentCount = $this->colonyLibFactory->createColonyPopulationCalculator(
                $colony
            )->getFreeAssignmentCount();

            if ($freeAssignmentCount === 0) {
                break;
            }

            $count++;
            $crewAssignment->setSpacecraft(null);
            $crewAssignment->setSlot(null);
            $crewAssignment->setColony($colony);
            $escapePod->getCrewAssignments()->removeElement($crewAssignment);
            $colony->getCrewAssignments()->add($crewAssignment);
            $this->shipCrewRepository->save($crewAssignment);
        }

        return $count;
    }
}
