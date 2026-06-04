<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\LiveMap;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Repository\LayerRepositoryInterface;

final class ShowLiveMap implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ADMIN_LIVE_MAP';

    public function __construct(private LayerRepositoryInterface $layerRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        if (!$game->isAdmin()) {
            $game->getInfo()->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $layers = $this->layerRepository->findAllIndexed();
        $requestedLayerId = request::getInt('layerid');
        $layer = $requestedLayerId > 0
            ? ($layers[$requestedLayerId] ?? null)
            : current($layers);

        if (!$layer instanceof Layer) {
            $game->getInfo()->addInformation('Es existiert kein Kartenlayer');
            return;
        }

        $game->setPageTitle(_('Admin: Livekarte'));
        $game->appendNavigationPart('/admin/?SHOW_ADMIN_LIVE_MAP=1', _('Livekarte'));
        $game->setTemplateFile('html/admin/liveMap.twig');
        $game->setTemplateVar('LAYERS', $layers);
        $game->setTemplateVar('LAYER', $layer);
        $game->setTemplateVar('CELL_SIZE', ShowLiveMapImage::CELL_SIZE);
    }
}
