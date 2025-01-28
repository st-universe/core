<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Component\Spacecraft\System\Exception\AlreadyOffException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class InterceptShipCore implements InterceptShipCoreInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private PrivateMessageSenderInterface $privateMessageSender,
        private EntityManagerInterface $entityManager
    ) {}

    #[Override]
    public function intercept(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftWrapperInterface $targetWrapper,
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

        $this->privateMessageSender->send(
            $userId,
            $target->getUser()->getId(),
            $pm,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $target
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

    /** @param ArrayCollection<int, SpacecraftWrapperInterface> $wrappersToToggleAlertReaction */
    private function deactivateWarpdrive(SpacecraftWrapperInterface $wrapper, Collection $wrappersToToggleAlertReaction, bool $addSelfAsToggle = false): void
    {
        try {
            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::WARPDRIVE);
            $this->spacecraftRepository->save($wrapper->get());

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
