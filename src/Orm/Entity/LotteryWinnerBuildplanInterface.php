<?php

namespace Stu\Orm\Entity;

interface LotteryWinnerBuildplanInterface
{

    public function getId(): int;

    public function getBuildplan(): ShipBuildplanInterface;

    public function getChance(): int;
}
