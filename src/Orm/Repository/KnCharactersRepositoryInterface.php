<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\KnCharacters;
use Stu\Orm\Entity\KnCharactersInterface;

/**
 * @extends ObjectRepository<KnCharacters>
 */
interface KnCharactersRepositoryInterface extends ObjectRepository
{
    public function prototype(): KnCharactersInterface;

    public function save(KnCharactersInterface $knCharacters): void;

    public function delete(KnCharactersInterface $knCharacters): void;

    /**
     * @return array<KnCharactersInterface>
     */
    public function getByKnId(int $knId): array;
}
