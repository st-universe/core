<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\KnCharacter;
use Stu\Orm\Entity\KnCharacterInterface;

/**
 * @extends ObjectRepository<KnCharacter>
 */
interface KnCharacterRepositoryInterface extends ObjectRepository
{
    public function prototype(): KnCharacterInterface;

    public function save(KnCharacterInterface $knCharacters): void;

    public function delete(KnCharacterInterface $knCharacters): void;

    /**
     * @return array<KnCharacterInterface>
     */
    public function getByKnId(int $knId): array;
}
