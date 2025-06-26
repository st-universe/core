<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Trumfield;

/**
 * @extends ObjectRepository<Trumfield>
 */
interface TrumfieldRepositoryInterface extends ObjectRepository
{
    public function prototype(): Trumfield;

    public function save(Trumfield $trumfield): void;

    public function delete(Trumfield $trumfield): void;
}
