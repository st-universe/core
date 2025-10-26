<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\KnCharacter;

/**
 * @extends EntityRepository<KnCharacter>
 */
final class KnCharacterRepository extends EntityRepository implements KnCharacterRepositoryInterface
{
    #[\Override]
    public function prototype(): KnCharacter
    {
        return new KnCharacter();
    }

    #[\Override]
    public function save(KnCharacter $knCharacters): void
    {
        $em = $this->getEntityManager();
        $em->persist($knCharacters);
        $em->flush();
    }

    #[\Override]
    public function delete(KnCharacter $knCharacters): void
    {
        $em = $this->getEntityManager();
        $em->remove($knCharacters);
        $em->flush();
    }

    #[\Override]
    public function getByKnId(int $knId): array
    {
        return $this->findBy(['knId' => $knId]);
    }
}
