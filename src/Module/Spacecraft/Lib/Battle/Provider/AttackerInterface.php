<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Provider;

use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Orm\Entity\LocationInterface;

interface AttackerInterface extends SpacecraftDestroyerInterface
{
    public function hasSufficientEnergy(int $amount): bool;

    public function getHitChance(): int;

    public function reduceEps(int $amount): void;

    public function getLocation(): LocationInterface;
}
