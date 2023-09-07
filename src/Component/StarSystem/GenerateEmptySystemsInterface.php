<?php

declare(strict_types=1);

namespace Stu\Component\StarSystem;

use Stu\Module\Control\GameControllerInterface;

interface GenerateEmptySystemsInterface
{
    public function generate(int $layerId, ?GameControllerInterface $game): int;
}
