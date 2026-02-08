<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;

class WarpcoreChargeTransferSystemData extends AbstractSystemData
{
    public function __construct(
        SpacecraftSystemRepositoryInterface $shipSystemRepository,
        StatusBarFactoryInterface $statusBarFactory
    ) {
        parent::__construct($shipSystemRepository, $statusBarFactory);
    }

    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::WARPCORE_CHARGE_TRANSFER;
    }

    public function isUseable(): bool
    {
        return $this->spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPCORE_CHARGE_TRANSFER)->getMode()->isActivated();
    }
}
