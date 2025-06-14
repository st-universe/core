<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<Layer>
 *
 * @method null|LayerInterface find(integer $id)
 */
interface LayerRepositoryInterface extends ObjectRepository
{
    public function prototype(): LayerInterface;

    public function save(LayerInterface $layer): void;

    public function delete(LayerInterface $layer): void;

    /**
     * @return array<int, LayerInterface>
     */
    public function findAllIndexed(): array;

    /**
     * @return array<int, LayerInterface>
     */
    public function getKnownByUser(UserInterface $user): array;

    public function getDefaultLayer(): LayerInterface;
}
