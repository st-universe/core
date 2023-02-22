<?php

namespace Stu\Module\Colony\View\ShowBeamTo;

interface ShowBeamToRequestInterface
{
    public function getColonyId(): int;

    public function getShipId(): int;
}
