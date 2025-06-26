<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Faction;

/**
 * @extends ObjectRepository<Faction>
 *
 * @method null|Faction find(integer $id)
 * @method Faction[] findAll()
 */
interface FactionRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<Faction>
     */
    public function getByChooseable(bool $chooseable): array;

    /**
     * Returns the playable factions and their current player count, indexed by faction id
     *
     * @return array<int, array{faction: Faction, count: int}>
     */
    public function getPlayableFactionsPlayerCount(): array;

    public function save(Faction $faction): void;
}
