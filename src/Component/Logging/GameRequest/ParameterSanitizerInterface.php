<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest;

use Stu\Orm\Entity\GameRequestInterface;

interface ParameterSanitizerInterface
{
    public function sanitize(GameRequestInterface $gameRequest): GameRequestInterface;
}
