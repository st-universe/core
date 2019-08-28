<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ScrollBuildMenu;

use Building;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowBuildMenuPart\ShowBuildMenuPart;

final class ScrollBuildMenu implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_SCROLL_BUILDMENU';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $menu = request::getIntFatal('menu');
        $offset = request::getInt('offset');
        if ($offset < 0) {
            $offset = 0;
        }
        if ($offset % BUILDMENU_SCROLLOFFSET != 0) {
            $offset = floor($offset / BUILDMENU_SCROLLOFFSET);
        }
        $ret = Building::getBuildingMenuList($colony->getId(), $menu, $offset);
        if (count($ret) == 0) {
            $ret = Building::getBuildingMenuList($colony->getId(), $menu, $offset - BUILDMENU_SCROLLOFFSET);
            $offset -= BUILDMENU_SCROLLOFFSET;
        }
        $arr['buildings'] = &$ret;
        $game->setTemplateVar('menu', $arr);
        $game->setTemplateVar('menutype', $menu);
        $game->setTemplateVar('scrolloffset', $offset);
        $game->setView(ShowBuildMenuPart::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
