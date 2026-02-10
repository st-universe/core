<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Overview;

use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class Overview implements ViewControllerInterface
{
    public function __construct(
        private readonly StuConfigInterface $config
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart('/admin/', _('Übersicht'));
        $game->setTemplateFile('html/admin/overview.twig');
        $game->setPageTitle(_('Admin'));

        $this->setWeatherReport($game);
    }

    private function setWeatherReport(GameControllerInterface $game): void
    {
        // load event map from file
        $historyFolder = $this->config->getGameSettings()->getTempDir() . '/history';

        $fileName = $historyFolder . '/ionstorm_map_layer_2.gif';
        // check if file exists
        if (!file_exists($fileName)) {
            return;
        }

        $fileContent = file_get_contents($fileName);
        if ($fileContent === false) {
            return;
        }

        $game->setTemplateVar(
            'ION_STORM_MAP',
            '<img src="data:image/gif;base64,' . base64_encode($fileContent) . '"/>'
        );
    }
}
