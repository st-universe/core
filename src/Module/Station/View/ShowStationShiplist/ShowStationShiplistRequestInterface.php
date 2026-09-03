<?php

declare(strict_types=1);

namespace Stu\Module\Station\View\ShowStationShiplist;

interface ShowStationShiplistRequestInterface
{
    public function getStationId(): int;
}
