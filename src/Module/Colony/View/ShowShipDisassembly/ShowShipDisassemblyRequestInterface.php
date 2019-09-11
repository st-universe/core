<?php

namespace Stu\Module\Colony\View\ShowShipDisassembly;

interface ShowShipDisassemblyRequestInterface
{
    public function getColonyId(): int;

    public function getFieldId(): int;
}