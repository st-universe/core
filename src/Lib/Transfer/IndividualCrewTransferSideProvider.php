<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Stu\Component\Crew\CrewTypeEnum;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;

final class IndividualCrewTransferSideProvider
{
    public function __construct(private ShipRumpCategoryRoleCrewRepositoryInterface $positionRepository) {}

    public function isSupported(StorageEntityWrapperInterface $source, StorageEntityWrapperInterface $target): bool
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
    public function getSide(StorageEntityWrapperInterface $wrapper, User $user, bool $isTarget): array
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
}
