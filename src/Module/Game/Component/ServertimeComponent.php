<?php

declare(strict_types=1);

namespace Stu\Module\Game\Component;

use Noodlehaus\ConfigInterface;
use Stu\Lib\Component\ComponentInterface;
use Stu\Module\Game\Lib\GameTurnProviderInterface;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;

/**
 * Renders the user box in the header
 */
final class ServertimeComponent implements ComponentInterface
{
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly GameTurnProviderInterface $gameTurnProvider
    ) {}

    #[\Override]
    public function setTemplateVariables(User $user, TemplateInterface $template): void
    {
        $template->setTemplateVar('GAMETURN', $this->gameTurnProvider->getCurrentRound()->getTurn());
        $template->setTemplateVar('GAME_VERSION', $this->config->get('game.version'));
    }
}
