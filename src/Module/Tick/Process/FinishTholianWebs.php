<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class FinishTholianWebs implements ProcessTickHandlerInterface
{
    public function __construct(private TholianWebRepositoryInterface $tholianWebRepository, private TholianWebUtilInterface $tholianWebUtil, private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory, private LeaveFleetInterface $leaveFleet, private SpacecraftSystemManagerInterface $spacecraftSystemManager, private PrivateMessageSenderInterface $privateMessageSender) {}

    #[Override]
    public function work(): void
    {
        foreach ($this->tholianWebRepository->getFinishedWebs() as $web) {
            //remove captured ships from fleet
            $this->handleFleetConstellations($web);

            //cancel tractor beams
            $this->cancelTractorBeams($web);

            //free helper
            $this->tholianWebUtil->resetWebHelpers($web, $this->spacecraftWrapperFactory, true);

            //set finished
            $web->setFinishedTime(null);
            $this->tholianWebRepository->save($web);
        }
    }

    private function handleFleetConstellations(TholianWebInterface $web): void
    {
        $fleets = [];

        //determine constellations
        foreach ($web->getCapturedShips() as $ship) {
            $fleet = $ship->getFleet();

            if ($fleet === null) {
                continue;
            }

            if (!array_key_exists($fleet->getId(), $fleets)) {
                $fleets[$fleet->getId()] = [];
            }

            $fleets[$fleet->getId()][] = $ship;
        }

        $pms = [];

        //modify constellation
        foreach ($fleets as $shiplist) {
            $fleet = current($shiplist)->getFleet();

            if ($fleet === null) {
                throw new RuntimeException('this should not happen');
            }

            //all ships of fleet in web, nothing to do
            if ($fleet->getShipCount() === count($shiplist)) {
                continue;
            }

            $userId = $fleet->getUser()->getId();
            $fleetName = $fleet->getName();

            /**
             * @var ShipInterface[] $shiplist
             */
            foreach ($shiplist as $ship) {
                $this->leaveFleet->leaveFleet($ship);

                if (!array_key_exists($userId, $pms)) {
                    $pms[$userId] = sprintf(_('Das Energienetz in Sektor %s wurde fertiggestellt') . "\n", $ship->getSectorString());
                }

                $pms[$userId] .= sprintf('Die %s hat die Flotte %s verlassen' . "\n", $ship->getName(), $fleetName);
            }
        }

        //notify target owners
        foreach ($pms as $recipientId => $pm) {
            $this->privateMessageSender->send(
                $web->getUser()->getId(),
                $recipientId,
                $pm,
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP
            );
        }
    }

    private function cancelTractorBeams(TholianWebInterface $web): void
    {
        $webOwner = $web->getUser();

        foreach ($web->getCapturedShips() as $ship) {
            if ($ship->isTractoring()) {
                $this->cancelTractorBeam($webOwner, $ship);
            }
            $tractoringSpacecraft = $ship->getTractoringSpacecraft();
            if ($tractoringSpacecraft !== null) {
                $this->cancelTractorBeam($webOwner, $tractoringSpacecraft);
            }
        }
    }

    private function cancelTractorBeam(UserInterface $webOwner, SpacecraftInterface $spacecraft): void
    {
        $this->privateMessageSender->send(
            $webOwner->getId(),
            $spacecraft->getUser()->getId(),
            sprintf(
                'Der Traktorstrahl der %s auf die %s wurde in Sektor %s durch ein Energienetz unterbrochen',
                $spacecraft->getName(),
                $spacecraft->getTractoredShip(),
                $spacecraft->getSectorString()
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );

        $this->spacecraftSystemManager->deactivate(
            $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft),
            SpacecraftSystemTypeEnum::SYSTEM_TRACTOR_BEAM,
            true
        ); //forced deactivation
    }
}
