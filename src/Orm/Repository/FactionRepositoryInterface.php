<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\FactionInterface;

/**
 * @extends ObjectRepository<Faction>
 *
 * @method null|FactionInterface find(integer $id)
 * @method FactionInterface[] findAll()
 */
interface FactionRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<FactionInterface>
     */
    public function getByChooseable(bool $chooseable): array;

    /**
     * Returns the playable factions and their current player count, indexed by faction id
     *
     * @return array<int, array{faction: FactionInterface, count: int}>
     */
    public function getPlayableFactionsPlayerCount(): array;

    public function save(FactionInterface $faction): void;
}
