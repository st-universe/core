<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Strategy;

use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

interface TransferStrategyInterface
{
    public function setTemplateVariables(
        bool $isUnload,
        ShipInterface|ColonyInterface $source,
        ShipInterface|ColonyInterface $target,
        GameControllerInterface $game
    ): void;

    public function transfer(
        bool $isUnload,
        ShipWrapperInterface $wrapper,
        ShipInterface|ColonyInterface $target,
        InformationWrapper $informations
    ): void;
}
