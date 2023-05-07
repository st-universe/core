<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Game\TimeConstants;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\SpacecraftTypeEnum;

final class ShipNfsItem
{
    private array $values;

    private int $userId;

    public function __construct(
        array $values,
        int $userId
    ) {
        $this->values = $values;
        $this->userId = $userId;
    }

    public function getId(): int
    {
        return $this->values['shipid'];
    }
    public function getFleetId(): ?int
    {
        return $this->values['fleetid'];
    }
    public function getName(): string
    {
        return $this->values['shipname'];
    }
    public function getHull(): int
    {
        return $this->values['hull'];
    }
    public function getMaxHull(): int
    {
        return $this->values['maxhull'];
    }
    public function getShield(): int
    {
        return $this->values['shield'];
    }
    public function getShieldState(): bool
    {
        return $this->values['shieldstate'] > 1;
    }
    public function getCloakState(): bool
    {
        return $this->values['cloakstate'] > 1;
    }
    public function getWarpState(): bool
    {
        return $this->values['warpstate'] > 1;
    }
    public function tractorbeamNotPossible(): bool
    {
        return $this->isBase() || $this->isTrumfield() || $this->getCloakState() || $this->getShieldState() || $this->getWarpState();
    }
    public function isInterceptable(): bool
    {
        return $this->getWarpState();
    }
    public function isDestroyed(): bool
    {
        return $this->values['isdestroyed'];
    }
    public function isBase(): bool
    {
        return $this->values['spacecrafttype'] === SpacecraftTypeEnum::SPACECRAFT_TYPE_STATION;
    }
    public function isTrumfield(): bool
    {
        return $this->values['rumpcategoryid'] === ShipRumpEnum::SHIP_CATEGORY_DEBRISFIELD;;
    }
    public function isShuttle(): bool
    {
        return $this->values['rumpcategoryid'] === ShipRumpEnum::SHIP_CATEGORY_SHUTTLE;
    }
    public function getRumpId(): int
    {
        return $this->values['rumpid'];
    }
    public function getFormerRumpId(): int
    {
        return $this->values['formerrumpid'];
    }
    public function getHoldingWebBackgroundStyle(): string
    {
        if ($this->values['webid'] === null) {
            return '';
        }

        $finishTime = $this->values['webfinishtime'];

        if ($finishTime === null || $finishTime < time()) {
            $icon =  'web.png';
        } else {
            $closeTofinish = $finishTime - time() < TimeConstants::ONE_HOUR_IN_SECONDS;

            if ($closeTofinish) {
                $icon = 'web_u.png';
            } else {
                $icon = 'web_u2.png';
            }
        }

        return sprintf('background-image: url(assets/buttons/%s); vertical-align: middle; text-align: center;', $icon);
    }
    public function getRumpName(): string
    {
        return $this->values['rumpname'];
    }
    public function getUserId(): int
    {
        return $this->values['userid'];
    }
    public function getUserName(): string
    {
        return $this->values['username'];
    }
    public function isOwnedByCurrentUser(): bool
    {
        return $this->userId == $this->getUserId();
    }

    public function hasUplink(): bool
    {
        return $this->values['uplinkstate'] > 0;
    }

    public function getRump(): mixed
    {
        return $this;
    }

    public function hasLogBook(): bool
    {
        return $this->values['haslogbook'];
    }
}
