<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use JsonException;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;

final class IndividualCrewTransfer
{
    public function __construct(
        private ShipRumpCategoryRoleCrewRepositoryInterface $positionRepository,
        private UserCrewRankRepositoryInterface $rankRepository,
        private CrewAssignmentRepositoryInterface $assignmentRepository,
        private TroopTransferUtilityInterface $troopTransferUtility
    ) {}

    public function setTemplateVariables(
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        ViewControllerContext $game
    ): void {
        if (!$this->isSupported($source, $target)) {
            $game->setMacroInAjaxWindow('');
            $game->getInfo()->addInformation('Einzelne Crewman können hier nicht transferiert werden');
            return;
        }

        $user = $game->getUser();
        $game->setPageTitle('Einzelne Crewman transferieren');
        $game->setMacroInAjaxWindow('html/transfer/individualCrewTransfer.twig');
        $game->setTemplateVar('CREW_TRANSFER_SIDES', [
            $this->getSide($source, $user, false),
            $this->getSide($target, $user, true)
        ]);
        $rankNames = [];
        foreach (CrewSkillLevelEnum::cases() as $rank) {
            $rankNames[$rank->value] = $this->rankRepository->getRankName($user, $rank);
        }
        $game->setTemplateVar('CREW_RANK_NAMES', $rankNames);
    }

    private function isSupported(StorageEntityWrapperInterface $source, StorageEntityWrapperInterface $target): bool
    {
        $sourceEntity = $source->get();
        $targetEntity = $target->get();

        return $sourceEntity !== $targetEntity
            && ($sourceEntity instanceof Spacecraft || $sourceEntity instanceof Colony)
            && ($targetEntity instanceof Spacecraft || $targetEntity instanceof Colony)
            && ($sourceEntity instanceof Spacecraft || $targetEntity instanceof Spacecraft)
            && ($source->getUser()->getId() === $target->getUser()->getId()
                || ($targetEntity instanceof Spacecraft && $targetEntity->hasUplink()));
    }

    /**
     * @return array{
     *     entity: EntityWithStorageInterface,
     *     positions: array<int, array{position: CrewTypeEnum, capacity: int|null, crewAssignments: list<CrewAssignment>, occupiedCount: int}>,
     *     count: int,
     *     fixedCount: int,
     *     minimum: int,
     *     maximum: int,
     *     ownsEntity: bool
     * }
     */
    private function getSide(StorageEntityWrapperInterface $wrapper, User $user, bool $isTarget): array
    {
        $entity = $wrapper->get();
        $positions = [];
        $config = null;
        $hasPositions = false;
        if ($entity instanceof Spacecraft) {
            $hasPositions = true;
            $rump = $entity->getRump();
            $role = $rump->getShipRumpRole();
            if ($role !== null) {
                $config = $this->positionRepository->getByShipRumpCategoryAndRole(
                    $rump->getShipRumpCategory()->getId(),
                    $role->getId()
                );
            }
        }

        $crewBySlot = [];
        $occupancyBySlot = [];
        $ownCount = 0;
        foreach ($entity->getCrewAssignments() as $assignment) {
            $slot = $hasPositions ? ($assignment->getSlot() ?? CrewTypeEnum::CREWMAN) : CrewTypeEnum::CREWMAN;
            $occupancyBySlot[$slot->value] = ($occupancyBySlot[$slot->value] ?? 0) + 1;
            if ($assignment->getCrew()->getUserId() !== $user->getId()) {
                continue;
            }
            $crewBySlot[$slot->value][] = $assignment;
            $ownCount++;
        }

        foreach ($hasPositions ? CrewTypeEnum::getOrder() : [CrewTypeEnum::CREWMAN] as $position) {
            $positions[$position->value] = [
                'position' => $position,
                'capacity' => $position === CrewTypeEnum::CREWMAN ? null : ($config?->getCrewForPosition($position) ?? 0),
                'crewAssignments' => $crewBySlot[$position->value] ?? [],
                'occupiedCount' => $occupancyBySlot[$position->value] ?? 0
            ];
        }

        $count = $entity->getCrewAssignments()->count();

        return [
            'entity' => $entity,
            'positions' => $positions,
            'count' => $count,
            'fixedCount' => $count - $ownCount,
            'minimum' => $isTarget ? 0 : max(0, $count - $wrapper->getMaxTransferrableCrew(false, $user)),
            'maximum' => $count + $wrapper->getFreeCrewSpace($user),
            'ownsEntity' => $wrapper->getUser()->getId() === $user->getId()
        ];
    }

    public function transfer(
        string $placementsJson,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void {
        if (!$this->isSupported($source, $target)) {
            $information->addInformation('Einzelne Crewman können hier nicht transferiert werden');
            return;
        }

        try {
            $placements = json_decode($placementsJson, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $information->addInformation('Die Crewauswahl ist ungültig');
            return;
        }
        if (!is_array($placements) || !array_is_list($placements)) {
            $information->addInformation('Die Crewauswahl ist ungültig');
            return;
        }

        $user = $source->getUser();
        $wrappers = [$source, $target];
        $sides = [$this->getSide($source, $user, false), $this->getSide($target, $user, true)];
        $assignments = [];
        foreach ($sides as $sideIndex => $side) {
            foreach ($side['positions'] as $slot => $position) {
                foreach ($position['crewAssignments'] as $assignment) {
                    $assignments[$assignment->getCrew()->getId()] = [$assignment, $sideIndex, $slot];
                }
            }
        }

        $seen = [];
        $counts = [$sides[0]['fixedCount'], $sides[1]['fixedCount']];
        $slotCounts = [[], []];
        foreach ($sides as $sideIndex => $side) {
            foreach ($side['positions'] as $slot => $position) {
                $slotCounts[$sideIndex][$slot] = $position['occupiedCount'] - count($position['crewAssignments']);
            }
        }
        $arrivals = [0, 0];
        $changes = [];
        foreach ($placements as $input) {
            $placement = IndividualCrewPlacement::fromInput($input);
            if ($placement === null
                || !isset($assignments[$placement->id])
                || isset($seen[$placement->id])
                || !isset($sides[$placement->side]['positions'][$placement->slot])) {
                $information->addInformation('Die Crewauswahl ist ungültig. Bitte öffne das Transferfenster erneut');
                return;
            }
            [$assignment, $originalSide, $originalSlot] = $assignments[$placement->id];
            if (!$this->hasOriginalPlacement($placement->originalSide, $placement->originalSlot, $originalSide, $originalSlot)) {
                $information->addInformation('Die Crewzuordnung hat sich geändert. Bitte öffne das Transferfenster erneut');
                return;
            }
            $seen[$placement->id] = true;
            $side = $placement->side;
            $slot = $placement->slot;
            $counts[$side]++;
            $slotCounts[$side][$slot] = ($slotCounts[$side][$slot] ?? 0) + 1;
            if ($side !== $originalSide || $slot !== $originalSlot) {
                $changes[] = [$assignment, $originalSide, $side, CrewTypeEnum::from($slot)];
            }
            if ($side !== $originalSide) {
                $arrivals[$side]++;
            }
        }

        if (count($seen) !== count($assignments)) {
            $information->addInformation('Die Crewzusammensetzung hat sich geändert. Bitte öffne das Transferfenster erneut');
            return;
        }
        foreach ($sides as $sideIndex => $side) {
            if (!$this->isCrewCountValid($counts[$sideIndex], $side['minimum'], $side['maximum'])) {
                $information->addInformation('Mindestcrew oder Crewkapazität würden verletzt. Bitte passe die Auswahl an');
                return;
            }
            foreach ($side['positions'] as $slot => $position) {
                if (!$this->hasSlotCapacity($position['capacity'], $position['occupiedCount'], $slotCounts[$sideIndex][$slot] ?? 0)) {
                    $information->addInformation('Für den ausgewählten Crewposten ist kein Platz mehr frei');
                    return;
                }
            }
        }

        if ($changes === []) {
            $information->addInformation('Es wurden keine Änderungen ausgewählt');
            return;
        }

        $netToTarget = $counts[1] - $sides[1]['count'];
        foreach ($wrappers as $sideIndex => $wrapper) {
            $increase = $counts[$sideIndex] - $sides[$sideIndex]['count'];
            if (!$this->canTransferCrew($wrapper, $increase, $arrivals[$sideIndex], $user, $information)) {
                return;
            }
        }

        foreach ($changes as [$assignment, $originalSide, $side, $slot]) {
            $this->applyChange($assignment, $originalSide, $side, $slot, $source, $target);
        }

        $source->postCrewTransfer(0, $target, $information);
        $target->postCrewTransfer($sides[1]['ownsEntity'] ? 0 : $netToTarget, $source, $information);
        $this->reportTransfers($arrivals[0], $arrivals[1], $source, $target, $information);
    }

    private function hasOriginalPlacement(mixed $side, mixed $slot, int $originalSide, int $originalSlot): bool
    {
        return $side === $originalSide && $slot === $originalSlot;
    }

    private function isCrewCountValid(int $count, int $minimum, int $maximum): bool
    {
        return $count >= $minimum && $count <= $maximum;
    }

    private function hasSlotCapacity(?int $capacity, int $occupiedCount, int $slotCount): bool
    {
        return $capacity === null || $slotCount <= max($capacity, $occupiedCount);
    }

    private function canTransferCrew(StorageEntityWrapperInterface $wrapper, int $increase, int $arrivals, User $user, InformationInterface $information): bool
    {
        return $wrapper->checkCrewStorage(abs($increase), $increase < 0, $information)
            && ($arrivals === 0 || $wrapper->acceptsCrewFrom(max(0, $increase), $user, $information));
    }

    private function applyChange(
        CrewAssignment $assignment,
        int $originalSide,
        int $side,
        CrewTypeEnum $slot,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target
    ): void {
        $destination = ($side === 0 ? $source : $target)->get();
        if ($side !== $originalSide) {
            $this->troopTransferUtility->assignCrew($assignment, $destination, $destination instanceof Spacecraft ? $slot : null);
        } else {
            $assignment->setSlot($destination instanceof Spacecraft ? $slot : null);
            $this->assignmentRepository->save($assignment);
        }
    }

    private function reportTransfers(
        int $arrivalsAtSource,
        int $arrivalsAtTarget,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void {
        if ($arrivalsAtTarget > 0) {
            $information->addInformationf('%d Crew von %s zu %s transferiert', $arrivalsAtTarget, $source->getName(), $target->getName());
        }
        if ($arrivalsAtSource > 0) {
            $information->addInformationf('%d Crew von %s zu %s transferiert', $arrivalsAtSource, $target->getName(), $source->getName());
        }
    }
}
