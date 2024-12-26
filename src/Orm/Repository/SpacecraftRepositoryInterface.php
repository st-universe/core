<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<Spacecraft>
 *
 * @method null|SpacecraftInterface find(integer $id)
 * @method SpacecraftInterface[] findAll()
 */
interface SpacecraftRepositoryInterface extends ObjectRepository
{
    public function save(SpacecraftInterface $spacecraft): void;

    public function delete(SpacecraftInterface $spacecraft): void;

    public function getAmountByUserAndSpecialAbility(
        int $userId,
        int $specialAbilityId
    ): int;

    public function getAmountByUserAndRump(int $userId, int $spacecraftRumpId): int;

    /**
     * @return array<SpacecraftInterface>
     */
    public function getByUser(UserInterface $user): array;

    /**
     * @return array<SpacecraftInterface>
     */
    public function getSuitableForShieldRegeneration(int $regenerationThreshold): array;

    /**
     * @return array<SpacecraftInterface>
     */
    public function getPlayerSpacecraftsForTick(): iterable;

    /**
     * @return array<SpacecraftInterface>
     */
    public function getNpcSpacecraftsForTick(): array;

    public function isCloakedSpacecraftAtLocation(
        SpacecraftInterface $spacecraft
    ): bool;

    public function getRandomSpacecraftIdWithCrewByUser(int $userId): ?int;

    /**
     * @return array<SpacecraftInterface>
     */
    public function getAllTractoringSpacecrafts(): array;

    public function truncateAllSpacecrafts(): void;
}
