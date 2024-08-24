<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\KnCharacter;
use Stu\Orm\Entity\KnCharacterInterface;

/**
 * @extends EntityRepository<KnCharacter>
 */
final class KnCharacterRepository extends EntityRepository implements KnCharacterRepositoryInterface
{
    #[Override]
    public function prototype(): KnCharacterInterface
    {
        return new KnCharacter();
    }

    #[Override]
    public function save(KnCharacterInterface $knCharacters): void
    {
        $em = $this->getEntityManager();
        $em->persist($knCharacters);
        $em->flush();
    }

    #[Override]
    public function delete(KnCharacterInterface $knCharacters): void
    {
        $em = $this->getEntityManager();
        $em->remove($knCharacters);
        $em->flush();
    }

    #[Override]
    public function getByKnId(int $knId): array
    {
        return $this->findBy(['knId' => $knId]);
    }
}
