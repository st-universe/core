<?php

namespace Stu\Module\Tick\Ship\ManagerComponent;

use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class EscapePodHandling implements ManagerComponentInterface
{
    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipRemoverInterface $shipRemover;

    private ShipRepositoryInterface $shipRepository;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRemoverInterface $shipRemover,
        ShipRepositoryInterface $shipRepository,
        ShipCrewRepositoryInterface $shipCrewRepository,
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRemover = $shipRemover;
        $this->shipRepository = $shipRepository;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function work(): void
    {
        $escapedToColonies = [];

        foreach ($this->shipRepository->getEscapePods() as $escapePod) {
            if ($escapePod->getCrewCount() === 0) {
                $this->shipRemover->remove($escapePod);
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
                UserEnum::USER_NOONE,
                $colony->getUser()->getId(),
                $msg,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY
            );
        }
    }

    private function transferOwnCrewToColony(ShipInterface $escapePod, ColonyInterface $colony): int
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
            $crewAssignment->setShip(null);
            $crewAssignment->setSlot(null);
            $crewAssignment->setColony($colony);
            $escapePod->getCrewAssignments()->removeElement($crewAssignment);
            $colony->getCrewAssignments()->add($crewAssignment);
            $this->shipCrewRepository->save($crewAssignment);
        }

        return $count;
    }
}
