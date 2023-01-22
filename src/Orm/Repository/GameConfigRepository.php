<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\GameConfig;
use Stu\Orm\Entity\GameConfigInterface;

/**
 * @extends EntityRepository<GameConfig>
 */
final class GameConfigRepository extends EntityRepository implements GameConfigRepositoryInterface
{

    public function save(GameConfigInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
        $em->flush();
    }

    public function getByOption(int $optionId): ?GameConfigInterface
    {
        return $this->findOneBy([
            'option' => $optionId
        ]);
    }
}
