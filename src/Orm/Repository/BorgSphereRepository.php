<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Override;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\BorgSphereRaid;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;

final class BorgSphereRepository implements BorgSphereRepositoryInterface
{
    public function __construct(private EntityManagerInterface $em) {}
    #[Override]
    public function findConfiguration(int $id): ?BorgSphereConfiguration
    {
        return $this->em->find(BorgSphereConfiguration::class, $id);
    }
    #[Override]
    public function getConfigurations(): array
    {
        return $this->em->getRepository(BorgSphereConfiguration::class)->findBy([], ['id' => 'ASC']);
    }
    #[Override]
    public function getByShip(Spacecraft $ship): ?BorgSphereEvent
    {
        return $this->getByShipId($ship->getId());
    }
    #[Override]
    public function getByShipId(int $shipId): ?BorgSphereEvent
    {
        return $this->em->getRepository(BorgSphereEvent::class)->findOneBy(['shipId' => $shipId]);
    }
    #[Override]
    public function lockConfiguration(BorgSphereConfiguration $configuration): void
    {
        $this->em->lock($configuration, LockMode::PESSIMISTIC_WRITE);
    }
    #[Override]
    public function saveSpawn(BorgSphereEvent $event, Ship $ship): void
    {
        $this->em->persist($ship);
        $this->em->flush();
        $event->setShipId($ship->getId());
        $this->em->persist($event);
    }
    #[Override]
    public function getByConfiguration(BorgSphereConfiguration $configuration): ?BorgSphereEvent
    {
        return $this->em->getRepository(BorgSphereEvent::class)->findOneBy(['configuration' => $configuration]);
    }
    #[Override]
    public function getDue(int $now): array
    {
        return $this->em->createQuery('SELECT e FROM ' . BorgSphereEvent::class . ' e WHERE e.nextTickAt <= :now AND e.phase NOT IN (:finished)')
            ->setParameters(['now' => $now, 'finished' => [BorgSpherePhaseEnum::ESCAPED, BorgSpherePhaseEnum::DESTROYED]])
            ->setHint(Query::HINT_REFRESH, true)->setLockMode(LockMode::PESSIMISTIC_WRITE)->getResult();
    }
    #[Override]
    public function save(BorgSphereEvent $event): void
    {
        $this->em->persist($event);
    }
    #[Override]
    public function getRaid(User $user, bool $lock = false): ?BorgSphereRaid
    {
        if ($lock) {
            $this->em->lock($user, LockMode::PESSIMISTIC_WRITE);
        }
        $query = $this->em->createQuery('SELECT r FROM ' . BorgSphereRaid::class . ' r WHERE r.user = :user')->setParameter('user', $user);
        if ($lock) {
            $query->setHint(Query::HINT_REFRESH, true)->setLockMode(LockMode::PESSIMISTIC_WRITE);
        }
        return $query->getOneOrNullResult();
    }
    #[Override]
    public function saveRaid(BorgSphereRaid $raid): void
    {
        $this->em->persist($raid);
    }
    #[Override]
    public function deleteRaids(User $user): void
    {
        $this->em->createQuery('DELETE FROM ' . BorgSphereRaid::class . ' r WHERE r.user = :user')->setParameter('user', $user)->execute();
    }
    #[Override]
    public function lockEvent(BorgSphereEvent $event): void
    {
        $this->em->lock($event, LockMode::PESSIMISTIC_WRITE);
        $this->em->refresh($event);
    }
    #[Override]
    public function deleteByUser(User $user): void
    {
        $this->deleteRaids($user);
        foreach ($this->em->getRepository(BorgSphereConfiguration::class)->findBy(['user' => $user]) as $configuration) {
            $this->deleteConfiguration($configuration);
        }
    }
    #[Override]
    public function deleteByColony(Colony $colony): void
    {
        foreach ($this->em->getRepository(BorgSphereConfiguration::class)->findBy(['colony' => $colony]) as $configuration) {
            $this->deleteConfiguration($configuration);
        }
    }
    #[Override]
    public function deleteByQuest(NPCQuest $quest): void
    {
        foreach ($this->em->getRepository(BorgSphereConfiguration::class)->findBy(['quest' => $quest]) as $configuration) {
            $this->deleteConfiguration($configuration);
        }
    }
    #[Override]
    public function deleteConfiguration(BorgSphereConfiguration $configuration): void
    {
        $this->em->createQuery('DELETE FROM ' . BorgSphereEvent::class . ' e WHERE e.configuration = :configuration')->setParameter('configuration', $configuration)->execute();
        $this->em->remove($configuration);
    }
}
