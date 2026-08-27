<?php

namespace Stu\Orm\Repository;

use Stu\Component\Crew\CrewRaceUsageEnum;
use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\CrewRace;

/**
 * @extends ObjectRepository<CrewRace>
 *
 * @method null|CrewRace find(integer $id)
 */
interface CrewRaceRepositoryInterface extends ObjectRepository
{
    public function prototype(): CrewRace;

    public function save(CrewRace $crewRace): void;

    /**
     * @return list<CrewRace>
     */
    public function getByFaction(int $factionId): array;

    /**
     * @return list<CrewRace>
     */
    public function getForUser(int $userId, int $factionId, CrewRaceUsageEnum $usage): array;

    /**
     * @return list<CrewRace>
     */
    public function getByCreatorUserId(int $userId): array;

    /**
     * @return list<CrewRace>
     */
    public function getPendingCustomRaces(): array;

    /**
     * @return list<CrewRace>
     */
    public function getAcceptedCustomRaces(): array;

    /**
     * @return list<CrewRace>
     */
    public function getRejectedCustomRaces(): array;

    public function getByGfxPath(string $gfxPath): ?CrewRace;
}
