<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\TholianWeb;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class FinishTholianWebs implements ProcessTickHandlerInterface
{
    public function __construct(
        private TholianWebRepositoryInterface $tholianWebRepository,
        private TholianWebUtilInterface $tholianWebUtil,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private LeaveFleetInterface $leaveFleet,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[\Override]
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

    private function handleFleetConstellations(TholianWeb $web): void
    {
        /** @var array<int, array<Ship>> */
        $fleets = [];

        //determine constellations
        foreach ($web->getCapturedSpacecrafts() as $spacecraft) {

            if (!$spacecraft instanceof Ship) {
                continue;
            }

            $fleet = $spacecraft->getFleet();
            if ($fleet === null) {
                continue;
            }

            if (!array_key_exists($fleet->getId(), $fleets)) {
                $fleets[$fleet->getId()] = [];
            }

            $fleets[$fleet->getId()][] = $spacecraft;
        }

        $pms = [];

        //modify constellation
        foreach ($fleets as $shiplist) {

            $current = current($shiplist);
            $fleet = $current ? $current->getFleet() : null;

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
             * @var Ship[] $shiplist
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

    private function cancelTractorBeams(TholianWeb $web): void
    {
        $webOwner = $web->getUser();

        foreach ($web->getCapturedSpacecrafts() as $spacecraft) {
            if ($spacecraft->isTractoring()) {
                $this->cancelTractorBeam($webOwner, $spacecraft);
            }

            $tractoringSpacecraft = $spacecraft instanceof Ship ? $spacecraft->getTractoringSpacecraft() : null;
            if ($tractoringSpacecraft !== null) {
                $this->cancelTractorBeam($webOwner, $tractoringSpacecraft);
            }
        }
    }

    private function cancelTractorBeam(User $webOwner, Spacecraft $spacecraft): void
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
            SpacecraftSystemTypeEnum::TRACTOR_BEAM,
            true
        ); //forced deactivation
    }
}
