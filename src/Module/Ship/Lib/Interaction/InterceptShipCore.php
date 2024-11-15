<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Interaction;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Component\Ship\System\Exception\AlreadyOffException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class InterceptShipCore implements InterceptShipCoreInterface
{
    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private ShipSystemManagerInterface $shipSystemManager,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private PrivateMessageSenderInterface $privateMessageSender,
        private EntityManagerInterface $entityManager
    ) {}

    #[Override]
    public function intercept(
        ShipWrapperInterface $wrapper,
        ShipWrapperInterface $targetWrapper,
        InformationInterface $informations
    ): void {

        $ship = $wrapper->get();
        $target = $targetWrapper->get();
        $userId = $ship->getUser()->getId();

        $wrappersToToggleAlertReaction = new ArrayCollection([$targetWrapper]);

        $targetFleetWrapper = $targetWrapper->getFleetWrapper();
        if ($targetFleetWrapper !== null) {
            foreach ($targetFleetWrapper->getShipWrappers() as $fleetWrapper) {
                $this->deactivateWarpdrive($fleetWrapper, $wrappersToToggleAlertReaction);
            }

            $informations->addInformationf("Die Flotte %s wurde abgefangen", $targetFleetWrapper->get()->getName());
            $pm = "Die Flotte " . $targetFleetWrapper->get()->getName() . " wurde von der " . $ship->getName() . " abgefangen";
        } else {
            $this->deactivateWarpdrive($targetWrapper, $wrappersToToggleAlertReaction);

            $informations->addInformationf("Die %s wurde abgefangen", $target->getName());
            $pm = "Die " . $target->getName() . " wurde von der " . $ship->getName() . " abgefangen";
        }

        $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $target->getId());

        $this->privateMessageSender->send(
            $userId,
            $target->getUser()->getId(),
            $pm,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $href
        );

        $fleetWrapper = $wrapper->getFleetWrapper();
        if ($fleetWrapper !== null) {
            foreach ($fleetWrapper->getShipWrappers() as $fleetWrapper) {
                $this->deactivateWarpdrive($fleetWrapper, $wrappersToToggleAlertReaction);
            }
        } else {
            $this->deactivateWarpdrive($wrapper, $wrappersToToggleAlertReaction, true);
        }
        $this->entityManager->flush();

        // alert reaction check
        foreach ($wrappersToToggleAlertReaction as $wrapper) {
            $this->alertReactionFacade->doItAll($wrapper, $informations);
        }
    }

    /** @param ArrayCollection<int, ShipWrapperInterface> $wrappersToToggleAlertReaction */
    private function deactivateWarpdrive(ShipWrapperInterface $wrapper, Collection $wrappersToToggleAlertReaction, bool $addSelfAsToggle = false): void
    {
        try {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
            $this->shipRepository->save($wrapper->get());

            $tractoredWrapper = $wrapper->getTractoredShipWrapper();
            if ($tractoredWrapper !== null) {
                $wrappersToToggleAlertReaction->add($tractoredWrapper);
            }
            if ($addSelfAsToggle) {
                $wrappersToToggleAlertReaction->add($wrapper);
            }
        } catch (AlreadyOffException) {
        }
    }
}
