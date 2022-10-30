<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\DealsInterface;

/**
 * @method null|DealsInterface find(integer $id)
 */
interface DealsRepositoryInterface extends ObjectRepository
{
    public function prototype(): DealsInterface;

    public function save(DealsInterface $post): void;

    public function delete(DealsInterface $post): void;
}