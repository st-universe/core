<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Alliance;

/**
 * @extends ObjectRepository<Alliance>
 *
 * @method null|Alliance find(integer $id)
 * @method Alliance[] findAll()
 */
interface AllianceRepositoryInterface extends ObjectRepository
{
    public function prototype(): Alliance;

    public function save(Alliance $post): void;

    public function delete(Alliance $post): void;

    /**
     * @return array<Alliance>
     */
    public function findAllOrdered(): array;

    /**
     * @return array<Alliance>
     */
    public function findByApplicationState(bool $acceptApplications): array;
}
