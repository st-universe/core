<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowHelp;

use Noodlehaus\ConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowHelp implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_HELP';

    public function __construct(private ConfigInterface $config)
    {
    }

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Hilfe - Star Trek Universe'));
        $game->setTemplateFile('html/index/help.twig');

        $game->setTemplateVar('FORUM', $this->config->get('board.base_url'));
        $game->setTemplateVar('WIKI', $this->config->get('wiki.base_url'));
        $game->setTemplateVar('CHAT', $this->config->get('discord.url'));
    }
}
