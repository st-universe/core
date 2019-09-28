<?php

declare(strict_types=1);

namespace Stu\Module\Trade;

use Stu\Component\Ship\System\ShieldShipSystem;
use Stu\Component\Ship\System\ShipSystemManager;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use function DI\autowire;
use function DI\create;

return [
    ShipSystemManagerInterface::class => create(ShipSystemManager::class)->constructor(
        [
            ShipSystemTypeEnum::SYSTEM_SHIELDS => autowire(ShieldShipSystem::class)
        ]
    )
];
