<?php

namespace Stu\Module\Ship\Action\JoinFleet;

interface JoinFleetRequestInterface
{
    public function getShipId(): int;

    public function getFleetId(): int;
}