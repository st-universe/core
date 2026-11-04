<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Entity\AwayMission;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyProtection;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\User;

final class ColonyProtectionRepository implements ColonyProtectionRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[\Override]
    public function isProtected(Colony $colony): bool
    {
        $protection = $this->entityManager->getRepository(ColonyProtection::class)->findOneBy(['colony' => $colony]);

        if ($protection !== null && $protection->isActive()) {
            return true;
        }
        $mission = $this->entityManager->getRepository(AwayMission::class)->findOneBy(['colony' => $colony]);

        return $mission !== null && $mission->getQuest()->getEnd() === null;
    }

    #[\Override]
    public function deleteByColony(Colony $colony): void
    {
        $this->entityManager->createQuery('DELETE FROM ' . ColonyProtection::class . ' p WHERE p.colony = :colony')
            ->setParameter('colony', $colony)->execute();
    }

    #[\Override]
    public function deleteByQuest(NPCQuest $quest): void
    {
        $this->entityManager->createQuery('DELETE FROM ' . ColonyProtection::class . ' p WHERE p.quest = :quest')
            ->setParameter('quest', $quest)->execute();
    }

    #[\Override]
    public function deleteByQuestOwner(User $user): void
    {
        $this->entityManager->createQuery(
            'DELETE FROM ' . ColonyProtection::class . ' p WHERE p.quest IN (SELECT q FROM ' . NPCQuest::class . ' q WHERE q.user = :user)'
        )->setParameter('user', $user)->execute();
    }
}
