<?php

declare(strict_types=1);

namespace Stu\Module\NPC\View\ShowTools;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Component\History\HistoryTypeEnum;

final class ShowTools implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TOOLS';

    public function __construct(private CommodityRepositoryInterface $commodityRepository, private LayerRepositoryInterface $layerRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        if ($game->isAdmin()) {
            $commodityList = $this->commodityRepository->getTradeableAdmin();
        } else {
            $commodityList = $this->commodityRepository->getTradeableNPC();
        }

        $historyTypes = [];
        foreach (HistoryTypeEnum::cases() as $type) {
            $historyTypes[] = [
                'id' => $type->value,
                'name' => $type->getName()
            ];
        }

        $layers = $this->layerRepository->findAllIndexed();

        $game->setTemplateVar('LAYERS', $layers);
        $game->setTemplateFile('html/npc/tools.twig');
        $game->appendNavigationPart('/npc/?SHOW_TOOLS=1', _('Tools'));
        $game->setPageTitle(_('Tools'));
        $game->setTemplateVar('SELECTABLE_COMMODITIES', $commodityList);
        $game->setTemplateVar('HISTORY_TYPES', $historyTypes);
    }
}
