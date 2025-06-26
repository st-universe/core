<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\KnCharacter;

/**
 * @extends ObjectRepository<KnCharacter>
 */
interface KnCharacterRepositoryInterface extends ObjectRepository
{
    public function prototype(): KnCharacter;

    public function save(KnCharacter $knCharacters): void;

    public function delete(KnCharacter $knCharacters): void;

    /**
     * @return array<KnCharacter>
     */
    public function getByKnId(int $knId): array;
}
