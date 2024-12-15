<?php

namespace Stu\Orm\Entity;

interface LotteryWinnerBuildplanInterface
{

    public function getId(): int;

    public function getBuildplan(): SpacecraftBuildplanInterface;

    public function getChance(): int;
}
