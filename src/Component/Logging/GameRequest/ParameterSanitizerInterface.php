<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest;

use Stu\Orm\Entity\GameRequest;

interface ParameterSanitizerInterface
{
    public function sanitize(GameRequest $gameRequest): GameRequest;
}
