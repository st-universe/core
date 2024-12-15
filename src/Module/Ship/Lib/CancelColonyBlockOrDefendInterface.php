<?php

namespace Stu\Module\Ship\Lib;

use Stu\Lib\Information\InformationInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface CancelColonyBlockOrDefendInterface
{
    public function work(
        SpacecraftInterface $spacecraft,
        InformationInterface $informations,
        bool $isTraktor = false
    ): void;
}
