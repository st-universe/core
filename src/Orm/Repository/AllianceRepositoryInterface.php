<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceInterface;

/**
 * @extends ObjectRepository<Alliance>
 *
 * @method null|AllianceInterface find(integer $id)
 * @method AllianceInterface[] findAll()
 */
interface AllianceRepositoryInterface extends ObjectRepository
{
    public function prototype(): AllianceInterface;

    public function save(AllianceInterface $post): void;

    public function delete(AllianceInterface $post): void;

    public function findAllOrdered(): array;

    public function findByApplicationState(bool $acceptApplications): array;
}
