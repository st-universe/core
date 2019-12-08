<?php

namespace Stu\Component\Player;

use Stu\Orm\Entity\UserInterface;

interface ColonyLimitCalculatorInterface
{
    public function canColonizeFurtherPlanets(UserInterface $user): bool;

    public function canColonizeFurtherMoons(UserInterface $user): bool;

    public function getPlanetColonyLimit(UserInterface $user): int;

    public function getMoonColonyLimit(UserInterface $user): int;

    public function getPlanetColonyCount(UserInterface $user): int;

    public function getMoonColonyCount(UserInterface $user): int;
}
