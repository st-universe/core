<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Overview;

use Stu\Component\Image\ImageCreationInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class Overview implements ViewControllerInterface
{
    public function __construct(
        private readonly ImageCreationInterface $imageCreation,
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

        // check if file exists
        if (!file_exists($historyFolder . '/ionstorm_map_layer_2.gif')) {
            return;
        }

        $graph = imagecreatefromgif($historyFolder . '/ionstorm_map_layer_2.gif');
        if ($graph === false) {
            return;
        }

        $game->setTemplateVar(
            'ION_STORM_MAP',
            $this->imageCreation->gdImageInSrc($graph, 'gif')
        );
    }
}
