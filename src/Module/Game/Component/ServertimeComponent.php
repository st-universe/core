<?php

declare(strict_types=1);

namespace Stu\Module\Game\Component;

use Noodlehaus\ConfigInterface;
use Override;
use Stu\Lib\Component\ComponentInterface;
use Stu\Module\Control\GameControllerInterface;

/**
 * Renders the user box in the header
 */
final class ServertimeComponent implements ComponentInterface
{
    public function __construct(private ConfigInterface $config) {}

    #[Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $game->setTemplateVar('GAMETURN', $game->getCurrentRound()->getTurn());
        $game->setTemplateVar('GAME_VERSION', $this->config->get('game.version'));
    }
}
