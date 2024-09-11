<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

interface InitializeShowTransferInterface
{
    public function init(
        ColonyInterface|ShipInterface $from,
        ColonyInterface|ShipInterface $to,
        bool $isUnload,
        TransferTypeEnum $transferType,
        GameControllerInterface $game
    ): void;
}
