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

    private function getSide(StorageEntityWrapperInterface $wrapper, User $user, bool $isTarget): array
    {
        $entity = $wrapper->get();
        $positions = [];
        $config = null;
        $hasPositions = false;
        if ($entity instanceof Spacecraft && $entity->getUser()->getId() === $user->getId()) {
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

        foreach ($hasPositions ? CrewTypeEnum::getOrder() : [CrewTypeEnum::CREWMAN] as $position) {
            $positions[$position->value] = [
                'position' => $position,
                'capacity' => $position === CrewTypeEnum::CREWMAN ? null : ($config?->getCrewForPosition($position) ?? 0),
                'crewAssignments' => []
            ];
        }

        $ownCount = 0;
        foreach ($entity->getCrewAssignments() as $assignment) {
            if ($assignment->getCrew()->getUserId() !== $user->getId()) {
                continue;
            }
            $slot = $hasPositions ? ($assignment->getSlot() ?? CrewTypeEnum::CREWMAN) : CrewTypeEnum::CREWMAN;
            $positions[$slot->value]['crewAssignments'][] = $assignment;
            $ownCount++;
        }

        $count = $entity->getCrewAssignments()->count();

        return [
            'entity' => $entity,
            'positions' => $positions,
            'count' => $count,
            'fixedCount' => $count - $ownCount,
            'minimum' => $isTarget ? 0 : max(0, $count - $wrapper->getMaxTransferrableCrew(false, $user)),
            'maximum' => $count + $wrapper->getFreeCrewSpace($user),
            'ownsEntity' => $entity->getUser()->getId() === $user->getId()
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
        $arrivals = [0, 0];
        $changes = [];
        foreach ($placements as $placement) {
            if (!is_array($placement)
                || !is_int($placement['id'] ?? null)
                || !is_int($placement['side'] ?? null)
                || !is_int($placement['slot'] ?? null)
                || !isset($assignments[$placement['id']])
                || isset($seen[$placement['id']])
                || !isset($sides[$placement['side']]['positions'][$placement['slot']])) {
                $information->addInformation('Die Crewauswahl ist ungültig. Bitte öffne das Transferfenster erneut');
                return;
            }
            [$assignment, $originalSide, $originalSlot] = $assignments[$placement['id']];
            if (($placement['originalSide'] ?? null) !== $originalSide
                || ($placement['originalSlot'] ?? null) !== $originalSlot) {
                $information->addInformation('Die Crewzuordnung hat sich geändert. Bitte öffne das Transferfenster erneut');
                return;
            }
            $seen[$placement['id']] = true;
            $side = $placement['side'];
            $slot = $placement['slot'];
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
            if ($counts[$sideIndex] < $side['minimum'] || $counts[$sideIndex] > $side['maximum']) {
                $information->addInformation('Mindestcrew oder Crewkapazität würden verletzt. Bitte passe die Auswahl an');
                return;
            }
            foreach ($side['positions'] as $slot => $position) {
                if ($position['capacity'] !== null
                    && ($slotCounts[$sideIndex][$slot] ?? 0) > max($position['capacity'], count($position['crewAssignments']))) {
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
            if (!$wrapper->checkCrewStorage(abs($increase), $increase < 0, $information)) {
                return;
            }
            if ($arrivals[$sideIndex] > 0 && !$wrapper->acceptsCrewFrom(max(0, $increase), $user, $information)) {
                return;
            }
        }

        foreach ($changes as [$assignment, $originalSide, $side, $slot]) {
            $destination = $wrappers[$side]->get();
            if ($side !== $originalSide) {
                $this->troopTransferUtility->assignCrew($assignment, $destination, $destination instanceof Spacecraft ? $slot : null);
            } else {
                $assignment->setSlot($destination instanceof Spacecraft ? $slot : null);
                $this->assignmentRepository->save($assignment);
            }
        }

        $source->postCrewTransfer(0, $target, $information);
        $target->postCrewTransfer($sides[1]['ownsEntity'] ? 0 : $netToTarget, $source, $information);
        if ($arrivals[1] > 0) {
            $information->addInformationf('%d Crew von %s zu %s transferiert', $arrivals[1], $source->getName(), $target->getName());
        }
        if ($arrivals[0] > 0) {
            $information->addInformationf('%d Crew von %s zu %s transferiert', $arrivals[0], $target->getName(), $source->getName());
        }
    }
}
