<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map;

use request;
use Stu\Component\Map\MapEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

final class ShowMapEditor implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MAP_EDITOR';

    private LayerRepositoryInterface $layerRepository;

    private StarSystemRepositoryInterface $starSystemRepository;

    public function __construct(
        LayerRepositoryInterface $layerRepository,
        StarSystemRepositoryInterface $starSystemRepository
    ) {
        $this->layerRepository = $layerRepository;
        $this->starSystemRepository = $starSystemRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/admin/mapeditor_overview.xhtml');
        $game->appendNavigationPart('/admin/?SHOW_MAP_EDITOR=1', _('Karteneditor'));
        $game->setPageTitle(_('Karteneditor'));

        //LAYER
        $layers = $this->layerRepository->findAllIndexed();

        $layerId = request::getInt('layerid');
        $layer = $layerId === 0 ? current($layers) : $layers[$layerId];
        $game->setTemplateVar('LAYERID', $layer->getId());

        //HEADROW
        $xHeadRow = [];
        for ($j = 1; $j <= (int)ceil($layer->getWidth() / MapEnum::FIELDS_PER_SECTION); $j++) {
            $xHeadRow[] = $j;
        }

        //SECTIONS
        $sections = [];
        $k = 1;
        for ($i = 1; $i <= (int)ceil($layer->getHeight() / MapEnum::FIELDS_PER_SECTION); $i++) {
            for ($j = 1; $j <= (int)ceil($layer->getWidth() / MapEnum::FIELDS_PER_SECTION); $j++) {
                $sections[$i][$j] = $k;
                $k++;
            }
        }

        $game->setTemplateVar('LAYERS', $layers);
        $game->setTemplateVar('X_HEAD_ROW', $xHeadRow);
        $game->setTemplateVar('SECTIONS', $sections);
        $game->setTemplateVar('FIELDS_PER_SECTION', MapEnum::FIELDS_PER_SECTION);
        $game->setTemplateVar('SYSTEM_LIST', $this->starSystemRepository->getByLayer($layer->getId()));
    }
}
