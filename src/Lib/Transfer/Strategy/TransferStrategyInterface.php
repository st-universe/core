<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Strategy;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

interface TransferStrategyInterface
{
    public function setTemplateVariables(
        bool $isUnload,
        ShipInterface $ship,
        ShipInterface|ColonyInterface $target,
        GameControllerInterface $game
    ): void;

    public function transfer(
        bool $isUnload,
        ShipWrapperInterface $wrapper,
        ShipInterface|ColonyInterface $target,
        GameControllerInterface $game
    ): void;
}
