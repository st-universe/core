<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowImprint;

use Noodlehaus\ConfigInterface;
use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowImprint implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_INFOS';

    public function __construct(private ConfigInterface $config)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Impressum - Star Trek Universe'));
        $game->setTemplateFile('html/index/impressum.twig');

        $game->setTemplateVar('IMPRINT_NAME', $this->config->get('game.imprint.name'));
        $game->setTemplateVar('IMPRINT_ADDRESS', $this->config->get('game.imprint.address'));
        $game->setTemplateVar('IMPRINT_ZIP', $this->config->get('game.imprint.zip'));
        $game->setTemplateVar('IMPRINT_CITY', $this->config->get('game.imprint.city'));
        $game->setTemplateVar('IMPRINT_COUNTRY', $this->config->get('game.imprint.country'));
        $game->setTemplateVar('IMPRINT_EMAIL', $this->config->get('game.imprint.email'));
        $game->setTemplateVar('IMPRINT_PHONE', $this->config->get('game.imprint.phone'));
    }
}
