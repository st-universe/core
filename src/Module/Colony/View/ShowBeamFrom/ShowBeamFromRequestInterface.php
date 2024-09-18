<?php

namespace Stu\Module\Colony\View\ShowBeamFrom;

interface ShowBeamFromRequestInterface
{
    public function getColonyId(): int;

    public function getShipId(): int;
}
