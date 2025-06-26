<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<Layer>
 *
 * @method null|Layer find(integer $id)
 */
interface LayerRepositoryInterface extends ObjectRepository
{
    public function prototype(): Layer;

    public function save(Layer $layer): void;

    public function delete(Layer $layer): void;

    /**
     * @return array<int, Layer>
     */
    public function findAllIndexed(): array;

    /**
     * @return array<int, Layer>
     */
    public function getKnownByUser(User $user): array;

    public function getDefaultLayer(): Layer;
}
