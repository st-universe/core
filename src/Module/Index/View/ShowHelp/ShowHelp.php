<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowHelp;

use Noodlehaus\ConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowHelp implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_HELP';

    private ConfigInterface $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Hilfe - Star Trek Universe'));
        $game->setTemplateFile('html/index_help.xhtml');

<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 4668b7d592ef1bbb971520a05a307d0b2b7d70cc
        $game->setTemplateVar('FORUM', $this->config->get('board.base_url'));
=======
        $game->setTemplateVar('BOARD', $this->config->get('board.base_url'));
>>>>>>> read config values
<<<<<<< HEAD
=======
        $game->setTemplateVar('BOARD', $this->config->get('board.base_url'));
>>>>>>> read config values
=======
>>>>>>> 4668b7d592ef1bbb971520a05a307d0b2b7d70cc
        $game->setTemplateVar('WIKI', $this->config->get('wiki.base_url'));
        $game->setTemplateVar('CHAT', $this->config->get('discord.url'));
    }
}
