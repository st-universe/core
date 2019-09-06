<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\GameConfigInterface;

interface GameConfigRepositoryInterface extends ObjectRepository
{
    public function save(GameConfigInterface $post): void;

    public function getByOption(int $optionId): ?GameConfigInterface;
}