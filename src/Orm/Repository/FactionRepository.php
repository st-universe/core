<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;

final class FactionRepository extends EntityRepository implements FactionRepositoryInterface
{
    public function getByChooseable(bool $chooseable): array
    {
        return $this->findBy([
            'chooseable' => $chooseable
        ]);
    }
}