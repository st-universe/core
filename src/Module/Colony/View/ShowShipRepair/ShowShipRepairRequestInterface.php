<?php

namespace Stu\Module\Colony\View\ShowShipRepair;

interface ShowShipRepairRequestInterface
{
    public function getColonyId(): int;

    public function getFieldId(): int;
}
