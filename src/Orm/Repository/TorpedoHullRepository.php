<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TorpedoHullInterface;
use Stu\Orm\Entity\TorpedoHull;


/**
 * @extends EntityRepository<TorpedoHull>
 */
final class TorpedoHullRepository extends EntityRepository implements TorpedoHullRepositoryInterface
{
    public function prototype(): TorpedoHullInterface
    {
        return new TorpedoHull();
    }

    public function save(TorpedoHullInterface $torpedohull): void
    {
        $em = $this->getEntityManager();

        $em->persist($torpedohull);
    }

    public function delete(TorpedoHullInterface $torpedohull): void
    {
        $em = $this->getEntityManager();

        $em->remove($torpedohull);
    }

    public function getByModuleAndTorpedo(
        int $moduleId,
        int $torpedoId
    ): ?TorpedoHullInterface {
        return $this->findOneBy([
            'module_id' => $moduleId,
            'torpedo_type' => $torpedoId
        ]);
    }
}
