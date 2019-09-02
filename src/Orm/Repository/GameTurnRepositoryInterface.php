<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\GameTurnInterface;

interface GameTurnRepositoryInterface extends ObjectRepository
{
    public function getCurrent(): GameTurnInterface;

    public function prototype(): GameTurnInterface;

    public function save(GameTurnInterface $turn): void;
}