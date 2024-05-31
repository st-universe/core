<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Game\TimeConstants;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\Type\TractorBeamShipSystem;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Battle\FightLib;

final class ShipNfsItem
{
    private TShipItemInterface $item;

    private int $userId;

    public function __construct(
        TShipItemInterface $item,
        int $userId
    ) {
        $this->item = $item;
        $this->userId = $userId;
    }

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
    public function getShieldState(): bool
    {
        return $this->item->getShieldState() > ShipSystemModeEnum::MODE_OFF;
    }
    public function getCloakState(): bool
    {

        return $this->item->getCloakState() > ShipSystemModeEnum::MODE_OFF;
    }
    public function getWarpDriveState(): bool
    {
        return $this->item->getWarpDriveState() > ShipSystemModeEnum::MODE_OFF;
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
        return $this->getWarpDriveState();
    }
    public function isDestroyed(): bool
    {
        return $this->item->isDestroyed();
    }
    public function isBase(): bool
    {
        return $this->item->getSpacecraftType() === SpacecraftTypeEnum::SPACECRAFT_TYPE_STATION;
    }
    public function isTrumfield(): bool
    {
        return $this->item->getRumpCategoryId() === ShipRumpEnum::SHIP_CATEGORY_DEBRISFIELD;;
    }
    public function isShuttle(): bool
    {
        return $this->item->getRumpCategoryId() === ShipRumpEnum::SHIP_CATEGORY_SHUTTLE;
    }
    public function getRumpId(): int
    {
        return $this->item->getRumpId();
    }
    public function getFormerRumpId(): ?int
    {
        return $this->item->getFormerRumpId();
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
    public function isOwnedByCurrentUser(): bool
    {
        return $this->userId === $this->getUserId();
    }

    public function hasUplink(): bool
    {
        return $this->item->getUplinkState() > ShipSystemModeEnum::MODE_OFF;
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
}
