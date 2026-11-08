<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\BorgSphereRaid;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;

interface BorgSphereRepositoryInterface
{
    public function findConfiguration(int $id): ?BorgSphereConfiguration;
    /** @return list<BorgSphereConfiguration> */
    public function getConfigurations(): array;
    public function getByShip(Spacecraft $ship): ?BorgSphereEvent;
    public function getByShipId(int $shipId): ?BorgSphereEvent;
    public function lockConfiguration(BorgSphereConfiguration $configuration): void;
    public function saveSpawn(BorgSphereEvent $event, Ship $ship): void;
    public function getByConfiguration(BorgSphereConfiguration $configuration): ?BorgSphereEvent;
    /** @return list<BorgSphereEvent> */
    public function getDue(int $now): array;
    public function save(BorgSphereEvent $event): void;
    public function getRaid(User $user, bool $lock = false): ?BorgSphereRaid;
    public function saveRaid(BorgSphereRaid $raid): void;
    public function deleteConfiguration(BorgSphereConfiguration $configuration): void;
    public function deleteRaids(User $user): void;
    public function lockEvent(BorgSphereEvent $event): void;
    public function deleteByUser(User $user): void;
    public function deleteByColony(Colony $colony): void;
    public function deleteByQuest(NPCQuest $quest): void;
}
