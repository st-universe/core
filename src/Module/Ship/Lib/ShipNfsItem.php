<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\ShipRumpEnum;

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
    public function getHuell()
    {
        return $this->values['hull'];
    }
    public function getMaxHuell()
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
    public function traktorbeamNotPossible()
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
    public function getIsDestroyed()
    {
        return $this->values['isdestroyed'];
    }
    public function isBase()
    {
        return $this->values['isbase'];
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
}
