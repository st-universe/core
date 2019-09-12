<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ScrollBuildMenu;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowBuildMenuPart\ShowBuildMenuPart;
use Stu\Orm\Repository\BuildingRepositoryInterface;

final class ScrollBuildMenu implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SCROLL_BUILDMENU';

    private $colonyLoader;

    private $buildingRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        BuildingRepositoryInterface $buildingRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->buildingRepository = $buildingRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $menu = (int) request::getIntFatal('menu');
        $offset = request::getInt('offset');
        if ($offset < 0) {
            $offset = 0;
        }
        if ($offset % BUILDMENU_SCROLLOFFSET != 0) {
            $offset = floor($offset / BUILDMENU_SCROLLOFFSET);
        }
        $ret = $this->buildingRepository->getByColonyAndUserAndBuildMenu(
            (int) $colony->getId(),
            $userId,
            $menu,
            (int) $offset
        );
        if (count($ret) == 0) {
            $ret = $this->buildingRepository->getByColonyAndUserAndBuildMenu(
                (int) $colony->getId(),
                $userId,
                $menu,
                (int) ($offset - BUILDMENU_SCROLLOFFSET)
            );
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
