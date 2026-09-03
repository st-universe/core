<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib;

use Stu\Module\Control\GameControllerInterface;

interface GameSetupInterface
{
    public function setTemplateAndComponents(string $viewTemplate, GameControllerInterface $game): void;
}
