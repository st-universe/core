<?php

declare(strict_types=1);

namespace Stu\Lib\Component;

use Stu\Module\Control\GameControllerInterface;

interface ComponentInterface
{
    public function setTemplateVariables(GameControllerInterface $game): void;
}
