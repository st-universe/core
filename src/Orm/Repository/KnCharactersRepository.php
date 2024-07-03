<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\KnCharacters;
use Stu\Orm\Entity\KnCharactersInterface;

/**
 * @extends EntityRepository<KnCharacters>
 */
final class KnCharactersRepository extends EntityRepository implements KnCharactersRepositoryInterface
{
    #[Override]
    public function prototype(): KnCharactersInterface
    {
        return new KnCharacters();
    }

    #[Override]
    public function save(KnCharactersInterface $knCharacters): void
    {
        $em = $this->getEntityManager();
        $em->persist($knCharacters);
        $em->flush();
    }

    #[Override]
    public function delete(KnCharactersInterface $knCharacters): void
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
