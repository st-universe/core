<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Trumfield;
use Stu\Orm\Entity\TrumfieldInterface;

/**
 * @extends ObjectRepository<Trumfield>
 */
interface TrumfieldRepositoryInterface extends ObjectRepository
{
    public function prototype(): TrumfieldInterface;

    public function save(TrumfieldInterface $trumfield): void;

    public function delete(TrumfieldInterface $trumfield): void;
}
