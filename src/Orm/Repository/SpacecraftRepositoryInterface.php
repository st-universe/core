<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Module\Spacecraft\Lib\TSpacecraftItemInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
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
    public function getSuitableForShieldRegeneration(): array;

    /**
     * @return array<SpacecraftInterface>
     */
    public function getPlayerSpacecraftsForTick(): iterable;

    /** @return array<SpacecraftInterface> */
    public function getNpcSpacecraftsForTick(): array;

    public function isCloakedSpacecraftAtLocation(
        SpacecraftInterface $spacecraft
    ): bool;

    /** @return array<TSpacecraftItemInterface> */
    public function getSingleSpacecraftScannerResults(
        SpacecraftInterface $spacecraft,
        bool $showCloaked = false,
        MapInterface|StarSystemMapInterface|null $field = null
    ): array;

    public function getRandomSpacecraftWithCrewByUser(int $userId): ?SpacecraftInterface;

    /**
     * @return array<SpacecraftInterface>
     */
    public function getAllTractoringSpacecrafts(): array;

    public function truncateAllSpacecrafts(): void;
}
