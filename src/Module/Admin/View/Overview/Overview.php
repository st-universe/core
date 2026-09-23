<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Overview;

use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    public function __construct(
        private readonly LayerRepositoryInterface $layerRepository,
        private readonly StuConfigInterface $config
    ) {}

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $game->appendNavigationPart('/admin/', _('Übersicht'));
        $game->setTemplateFile('html/admin/overview.twig');
        $game->setPageTitle(_('Admin'));

        $this->setWeatherReport($game);
    }

    private function setWeatherReport(ViewControllerContext $game): void
    {
        // load event map from file
        $historyFolder = $this->config->getGameSettings()->getTempDir() . '/history';

        /** @var array<string, string> $maps */
        $maps = [];

        foreach ($this->layerRepository->findAllIndexed() as $layer) {
            $fileName = $historyFolder . '/ionstorm_map_layer_' . $layer->getId() . '.gif';
            // check if file exists
            if (!file_exists($fileName)) {
                continue;
            }

            $fileContent = file_get_contents($fileName);
            if ($fileContent === false) {
                continue;
            }

            $maps[$layer->getName()] = '<img src="data:image/gif;base64,' . base64_encode($fileContent) . '"/>';
        }

        $game->setTemplateVar(
            'ION_STORM_MAPS',
            $maps
        );
    }
}
