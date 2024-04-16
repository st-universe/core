<?php

declare(strict_types=1);

namespace Stu\Module\NPC\View\ShowTools;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class ShowTools implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TOOLS';

    private CommodityRepositoryInterface $commodityRepository;

    public function __construct(
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->commodityRepository = $commodityRepository;
    }

    public function handle(GameControllerInterface $game): void
    {

        $commodityList = $this->commodityRepository->getTradeableNPC();

        $game->setTemplateFile('html/npc/tools.twig');
        $game->appendNavigationPart('/npc/?SHOW_TOOLS=1', _('Tools'));
        $game->setPageTitle(_('Tools'));
        $game->setTemplateVar('SELECTABLE_COMMODITIES', $commodityList);
    }
}
