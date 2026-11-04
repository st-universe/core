<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Stu\Orm\Entity\AwayMission;
use Stu\Orm\Entity\AwayMissionDialogue;
use Stu\Orm\Entity\AwayMissionField;
use Stu\Orm\Entity\AwayMissionProgress;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\User;

final class AwayMissionRepository implements AwayMissionRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[\Override]
    public function getByColony(Colony $colony): ?AwayMission
    {
        return $this->entityManager->getRepository(AwayMission::class)->findOneBy(['colony' => $colony]);
    }

    /** @return list<AwayMissionField> */
    #[\Override]
    public function getFields(AwayMission $mission): array
    {
        return $this->entityManager->getRepository(AwayMissionField::class)->findBy(['mission' => $mission], ['fieldId' => 'ASC']);
    }

    /** @return array<int, array<int, AwayMissionDialogue>> */
    #[\Override]
    public function getDialogue(AwayMission $mission, int $factionId): array
    {
        $entries = $this->entityManager->createQuery(
            'SELECT d FROM ' . AwayMissionDialogue::class . ' d
             WHERE d.mission = :mission AND (d.faction IS NULL OR IDENTITY(d.faction) = :factionId)
             ORDER BY d.page ASC, d.position ASC, d.id ASC'
        )->setParameters(['mission' => $mission, 'factionId' => $factionId])->getResult();

        $pages = [];
        foreach ($entries as $entry) {
            if (!$entry instanceof AwayMissionDialogue) {
                continue;
            }
            $page = $entry->getPage();
            $position = $entry->getPosition();
            if (!isset($pages[$page][$position]) || $entry->getFaction() !== null) {
                $pages[$page][$position] = $entry;
            }
        }

        return $pages;
    }

    #[\Override]
    public function getProgress(AwayMission $mission, User $user, bool $forUpdate = false): ?AwayMissionProgress
    {
        $query = $this->entityManager->createQuery(
            'SELECT p FROM ' . AwayMissionProgress::class . ' p WHERE p.mission = :mission AND p.user = :user'
        )->setParameters(['mission' => $mission, 'user' => $user]);
        if ($forUpdate) {
            $this->entityManager->lock($user, LockMode::PESSIMISTIC_WRITE);
            $query->setHint(Query::HINT_REFRESH, true)->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }

        return $query->getOneOrNullResult();
    }

    #[\Override]
    public function initializeProgress(AwayMission $mission, User $user): AwayMissionProgress
    {
        $progress = $this->getProgress($mission, $user, true);
        if ($progress === null) {
            $progress = new AwayMissionProgress();
            $progress->setMission($mission)->setUser($user);
            $this->saveProgress($progress);
        }

        return $progress;
    }

    #[\Override]
    public function saveProgress(AwayMissionProgress $progress): void
    {
        $this->entityManager->persist($progress);
    }

    #[\Override]
    public function deleteByUser(User $user): void
    {
        $this->entityManager->createQuery('DELETE FROM ' . AwayMissionProgress::class . ' p WHERE p.user = :user')
            ->setParameter('user', $user)->execute();
    }

    #[\Override]
    public function deleteByQuestOwner(User $user): void
    {
        $missions = $this->entityManager->createQuery(
            'SELECT m FROM ' . AwayMission::class . ' m JOIN m.quest q WHERE q.user = :user'
        )->setParameter('user', $user)->getResult();
        foreach ($missions as $mission) {
            if ($mission instanceof AwayMission) {
                $this->delete($mission);
            }
        }
    }

    #[\Override]
    public function deleteByColony(Colony $colony): void
    {
        foreach ($this->entityManager->getRepository(AwayMission::class)->findBy(['colony' => $colony]) as $mission) {
            $this->delete($mission);
        }
    }

    #[\Override]
    public function deleteByQuest(NPCQuest $quest): void
    {
        foreach ($this->entityManager->getRepository(AwayMission::class)->findBy(['quest' => $quest]) as $mission) {
            $this->delete($mission);
        }
    }

    private function delete(AwayMission $mission): void
    {
        foreach ([AwayMissionProgress::class, AwayMissionDialogue::class, AwayMissionField::class] as $entityClass) {
            $this->entityManager->createQuery('DELETE FROM ' . $entityClass . ' e WHERE e.mission = :mission')
                ->setParameter('mission', $mission)->execute();
        }
        $this->entityManager->createQuery('DELETE FROM ' . AwayMission::class . ' m WHERE m.id = :id')
            ->setParameter('id', $mission->getId())->execute();
    }
}
