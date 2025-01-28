<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Strategy;

use Override;
use request;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\CrewAssignmentInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;

class TroopTransferStrategy implements TransferStrategyInterface
{
    public function __construct(
        private SpacecraftCrewCalculatorInterface $shipCrewCalculator,
        private TroopTransferUtilityInterface $troopTransferUtility,
    ) {}

    #[Override]
    public function setTemplateVariables(
        bool $isUnload,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        GameControllerInterface $game
    ): void {

        $user = $game->getUser();
        $targetEntity = $target->get();

        if (
            $targetEntity instanceof ShipInterface
            && $targetEntity->getBuildplan() !== null
        ) {
            $game->setTemplateVar('SHOW_TARGET_CREW', true);
            $game->setTemplateVar('ACTUAL_TARGET_CREW', $targetEntity->getCrewCount());
            $game->setTemplateVar('MINIMUM_TARGET_CREW', $targetEntity->getBuildplan()->getCrew());
            $game->setTemplateVar(
                'MAXIMUM_TARGET_CREW',
                $this->shipCrewCalculator->getMaxCrewCountByShip($targetEntity)
            );
        }

        $game->setTemplateVar('MAXIMUM', $this->getMaxTransferrableCrew($source, $target, $user, $isUnload));
    }

    private function getMaxTransferrableCrew(
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        UserInterface $user,
        bool $isUnload
    ): int {
        return min(
            $isUnload ? $source->getMaxTransferrableCrew(false, $user) : $target->getMaxTransferrableCrew(true, $user),
            $isUnload ?  $target->getFreeCrewSpace($user) : $source->getFreeCrewSpace($user)
        );
    }

    #[Override]
    public function transfer(
        bool $isUnload,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void {

        $user = $source->getUser();

        $amount = min(
            request::postInt('crewcount'),
            $this->getMaxTransferrableCrew($source, $target, $user, $isUnload)
        );

        if ($amount < 1) {
            $information->addInformation('Es konnten keine Crewman transferiert werden');
            return;
        }

        if (!$source->checkCrewStorage($amount, $isUnload, $information)) {
            return;
        }

        if ($isUnload && !$target->acceptsCrewFrom($amount, $user, $information)) {
            return;
        }

        $destination = $isUnload ? $target : $source;
        $crewAssignments = $isUnload ? $source->get()->getCrewAssignments() : $target->get()->getCrewAssignments();
        $filteredByUser = $crewAssignments->filter(fn(CrewAssignmentInterface $crewAssignment): bool => $crewAssignment->getCrew()->getUser() === $source->getUser())->toArray();
        $slice = array_slice($filteredByUser, 0, $amount);

        foreach ($slice as $crewAssignment) {
            $this->troopTransferUtility->assignCrew($crewAssignment, $destination->get());
        }

        $information->addInformationf(
            'Die %s hat %d Crewman %s der %s transferiert.',
            $source->getName(),
            $amount,
            $isUnload ? 'zu' : 'von',
            $target->getName()
        );

        $foreignCrewChangeAmount = $source->getUser() !== $target->getUser()
            ? ($isUnload ? $amount : -$amount)
            : 0;

        $source->postCrewTransfer(0, $target, $information);
        $target->postCrewTransfer($foreignCrewChangeAmount, $source, $information);
    }
}
