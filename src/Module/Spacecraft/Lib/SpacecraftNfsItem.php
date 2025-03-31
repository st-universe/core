<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Component\Game\TimeConstants;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\Type\TractorBeamShipSystem;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\Battle\FightLib;
use Stu\Module\Spacecraft\Lib\TSpacecraftItemInterface;

class SpacecraftNfsItem
{
    public function __construct(private TSpacecraftItemInterface $item, private int $userId) {}

    public function getId(): int
    {
        return $this->item->getShipId();
    }
    public function getFleetId(): ?int
    {
        return $this->item->getFleetId();
    }
    public function getName(): string
    {
        return $this->item->getShipName();
    }
    public function getHull(): int
    {
        return $this->item->getHull();
    }
    public function getMaxHull(): int
    {
        return $this->item->getMaxHull();
    }
    public function getShield(): int
    {
        return $this->item->getShield();
    }
    public function isShielded(): bool
    {
        return $this->item->isShielded() > SpacecraftSystemModeEnum::MODE_OFF->value;
    }
    public function isCloaked(): bool
    {
        return $this->item->isCloaked() > SpacecraftSystemModeEnum::MODE_OFF->value;
    }
    public function getWarpDriveState(): bool
    {
        return $this->item->getWarpDriveState() > SpacecraftSystemModeEnum::MODE_OFF->value;
    }
    public function isWarped(): bool
    {
        return $this->getWarpDriveState()
            || $this->item->getTractorWarpState() > SpacecraftSystemModeEnum::MODE_OFF->value;
    }
    public function isScanPossible(): bool
    {
        return !$this->isCloaked()
            && $this->getType() !== SpacecraftTypeEnum::THOLIAN_WEB;
    }
    public function isTractorbeamPossible(): bool
    {
        return TractorBeamShipSystem::isTractorBeamPossible($this);
    }
    public function isBoardingPossible(): bool
    {
        return FightLib::isBoardingPossible($this);
    }
    public function isInterceptable(): bool
    {
        //TODO can tractored ships be intercepted?!
        return $this->getWarpDriveState();
    }
    public function getType(): SpacecraftTypeEnum
    {
        return $this->item->getType();
    }
    public function isStation(): bool
    {
        return $this->item->getType() === SpacecraftTypeEnum::STATION;
    }
    public function isTrumfield(): bool
    {
        return false;
    }
    public function isShuttle(): bool
    {
        return $this->item->getRumpCategoryId() === SpacecraftRumpEnum::SHIP_CATEGORY_SHUTTLE;
    }
    public function getRumpId(): int
    {
        return $this->item->getRumpId();
    }
    public function getHoldingWebBackgroundStyle(): string
    {
        if ($this->item->getWebId() === null) {
            return '';
        }

        $finishTime = $this->item->getWebFinishTime();

        if ($finishTime === null || $finishTime < time()) {
            $icon =  'web.png';
        } else {
            $closeTofinish = $finishTime - time() < TimeConstants::ONE_HOUR_IN_SECONDS;

            $icon = $closeTofinish ? 'web_u.png' : 'web_u2.png';
        }

        return sprintf('background-image: url(assets/buttons/%s); vertical-align: middle; text-align: center;', $icon);
    }
    public function getRumpName(): string
    {
        return $this->item->getRumpName();
    }
    public function getUserId(): int
    {
        return $this->item->getUserId();
    }

    public function isContactable(): bool
    {
        return $this->getUserId() != UserEnum::USER_NOONE;
    }

    public function getUserName(): string
    {
        return $this->item->getUserName();
    }
    public function isSelectable(): bool
    {
        return $this->userId === $this->getUserId()
            && $this->getType()->getModuleView() !== null;
    }

    public function hasUplink(): bool
    {
        return $this->item->getUplinkState() > SpacecraftSystemModeEnum::MODE_OFF->value;
    }

    public function getRump(): mixed
    {
        return $this;
    }

    public function hasLogBook(): bool
    {
        return $this->item->hasLogBook();
    }

    public function hasCrew(): bool
    {
        return $this->item->hasCrew();
    }

    public function isTransferPossible(): bool
    {
        return $this->getType()->isTransferPossible();
    }

    public function getTransferEntityType(): TransferEntityTypeEnum
    {
        return $this->isStation()
            ? TransferEntityTypeEnum::STATION
            : TransferEntityTypeEnum::SHIP;
    }
}
