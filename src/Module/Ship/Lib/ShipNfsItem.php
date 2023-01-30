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

    public function getId()
    {
        return $this->values['shipid'];
    }
    public function getName()
    {
        return $this->values['shipname'];
    }
    public function getHull()
    {
        return $this->values['hull'];
    }
    public function getMaxHull()
    {
        return $this->values['maxhull'];
    }
    public function getShield()
    {
        return $this->values['shield'];
    }
    public function getShieldState()
    {
        return $this->values['shieldstate'] > 1;
    }
    public function getCloakState()
    {
        return $this->values['cloakstate'] > 1;
    }
    public function getWarpState()
    {
        return $this->values['warpstate'] > 1;
    }
    public function tractorbeamNotPossible()
    {
        return $this->isBase() || $this->isTrumfield() || $this->getCloakState() || $this->getShieldState() || $this->getWarpState();
    }
    public function canBeAttacked()
    {
        return !$this->isTrumfield() && !$this->getWarpState();
    }
    public function isInterceptable()
    {
        return $this->getWarpState();
    }
    public function isDestroyed()
    {
        return $this->values['isdestroyed'];
    }
    public function isBase()
    {
        return $this->values['spacecrafttype'] === SpacecraftTypeEnum::SPACECRAFT_TYPE_STATION;
    }
    public function isTrumfield()
    {
        return $this->values['rumpcategoryid'] === ShipRumpEnum::SHIP_CATEGORY_DEBRISFIELD;;
    }
    public function isShuttle()
    {
        return $this->values['rumpcategoryid'] === ShipRumpEnum::SHIP_CATEGORY_SHUTTLE;
    }
    public function getRumpId()
    {
        return $this->values['rumpid'];
    }
    public function getFormerRumpId()
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
    public function getRumpName()
    {
        return $this->values['rumpname'];
    }
    public function getUserId()
    {
        return $this->values['userid'];
    }
    public function getUserName()
    {
        return $this->values['username'];
    }
    public function isOwnedByCurrentUser()
    {
        return $this->userId == $this->getUserId();
    }

    public function hasUplink(): bool
    {
        return $this->values['uplinkstate'] > 0;
    }

    public function getRump()
    {
        return $this;
    }

    public function isAdventDoor(): ?int
    {
        return $this->values['rumproleid'] === ShipRumpEnum::SHIP_ROLE_ADVENT_DOOR ? (int)date("j") : null;
    }
}
