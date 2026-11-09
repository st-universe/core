<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Component\Spacecraft\System\Exception\AlreadyOffException;
use Stu\Component\Spacecraft\System\Exception\SpacecraftSystemException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;

final class InterceptShipCore implements InterceptShipCoreInterface
{
    public function __construct(
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private PrivateMessageSenderInterface $privateMessageSender,
        private EntityManagerInterface $entityManager
    ) {}

    #[Override]
    public function intercept(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftWrapperInterface $targetWrapper,
        InformationInterface $informations,
        bool $sourceCloakWasDeactivated = false,
        bool $onlySameLocation = false
    ): void {

        $ship = $wrapper->get();
        if ($sourceCloakWasDeactivated) {
            $this->addCloakDeactivationInformation($ship, $informations);
        } else {
            $this->deactivateCloak($wrapper, $informations);
        }

        $target = $targetWrapper->get();
        $userId = $ship->getUser()->getId();

        $wrappersToToggleAlertReaction = new ArrayCollection([$targetWrapper]);

        if ($ship->isWarped()) {
            $wrappersToToggleAlertReaction->add($wrapper);
        }

        $targetFleetWrapper = $targetWrapper->getFleetWrapper();
        if ($targetFleetWrapper !== null) {
            foreach ($this->fleetMembers($targetFleetWrapper, $ship, $onlySameLocation) as $fleetWrapper) {
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
            foreach ($this->fleetMembers($fleetWrapper, $ship, $onlySameLocation) as $fleetWrapper) {
                $this->deactivateWarpdrive($fleetWrapper, $wrappersToToggleAlertReaction);
            }
        } else {
            $this->deactivateWarpdrive($wrapper, $wrappersToToggleAlertReaction, true);
        }
        $this->entityManager->flush();

        $this->triggerAlertReactions($wrappersToToggleAlertReaction, $ship, $informations, $onlySameLocation);
    }

    /** @param Collection<int, SpacecraftWrapperInterface> $wrappers */
    private function triggerAlertReactions(Collection $wrappers, Spacecraft $source, InformationInterface $information, bool $onlySameLocation): void
    {
        $members = $wrappers->toArray();
        shuffle($members);
        foreach ($members as $wrapper) {
            if ($onlySameLocation && ($source->getCondition()->isDisabled() || $source->getCondition()->isDestroyed())) {
                break;
            }
            $this->alertReactionFacade->doItAll($wrapper, $information);
        }
    }

    /** @return Collection<int, ShipWrapperInterface> */
    private function fleetMembers(FleetWrapperInterface $fleet, Spacecraft $source, bool $onlySameLocation): Collection
    {
        $members = $fleet->getShipWrappers();
        return $onlySameLocation
            ? $members->filter(fn (ShipWrapperInterface $wrapper): bool => $this->isLocalMember($wrapper->get(), $source))
            : $members;
    }

    private function isLocalMember(Spacecraft $member, Spacecraft $source): bool
    {
        return $member->getLocation() === $source->getLocation() && !$member->isCloaked()
            && !$member->getCondition()->isDisabled() && !$member->getCondition()->isDestroyed()
            && !$member->getRump()->isEscapePods() && !$member->isRpgModuleInvisible()
            && !$member->getUser()->isVacationRequestOldEnough();
    }

    /** @param ArrayCollection<int, SpacecraftWrapperInterface> $wrappersToToggleAlertReaction */
    private function deactivateWarpdrive(SpacecraftWrapperInterface $wrapper, Collection $wrappersToToggleAlertReaction, bool $addSelfAsToggle = false): void
    {
        try {
            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::WARPDRIVE);

            $tractoredWrapper = $wrapper->getTractoredShipWrapper();
            if ($tractoredWrapper !== null && !$wrappersToToggleAlertReaction->contains($tractoredWrapper)) {
                $wrappersToToggleAlertReaction->add($tractoredWrapper);
            }
            if ($addSelfAsToggle && !$wrappersToToggleAlertReaction->contains($wrapper)) {
                $wrappersToToggleAlertReaction->add($wrapper);
            }
        } catch (AlreadyOffException) {
            // nothing to do here
        }
    }

    private function deactivateCloak(SpacecraftWrapperInterface $wrapper, InformationInterface $informations): void
    {
        $ship = $wrapper->get();
        if (!$ship->isCloaked()) {
            return;
        }

        try {
            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::CLOAK, true);
            $this->addCloakDeactivationInformation($ship, $informations);
        } catch (SpacecraftSystemException) {
        }
    }

    private function addCloakDeactivationInformation(Spacecraft $ship, InformationInterface $informations): void
    {
        $informations->addInformationf(
            _('%s: System %s deaktiviert'),
            $ship->getName(),
            SpacecraftSystemTypeEnum::CLOAK->getDescription()
        );
    }
}
