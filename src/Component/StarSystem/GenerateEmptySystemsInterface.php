<?php

declare(strict_types=1);

namespace Stu\Component\StarSystem;

use Stu\Module\Control\Component\ControllerContext;

interface GenerateEmptySystemsInterface
{
    public function generate(int $layerId, ?ControllerContext $game): int;
}
