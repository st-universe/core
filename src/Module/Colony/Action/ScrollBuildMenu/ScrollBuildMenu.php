<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ScrollBuildMenu;

use request;
use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowBuildMenuPart\ShowBuildMenuPart;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;

final class ScrollBuildMenu implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SCROLL_BUILDMENU';

    private ColonyLoaderInterface $colonyLoader;

    private BuildingRepositoryInterface $buildingRepository;

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
        if ($offset % ColonyEnum::BUILDMENU_SCROLLOFFSET != 0) {
            $offset = (int)floor($offset / ColonyEnum::BUILDMENU_SCROLLOFFSET);
        }
        /** @var BuildingInterface[] $ret */
        $ret = $this->buildingRepository->getByColonyAndUserAndBuildMenu(
            (int) $colony->getId(),
            $userId,
            $menu,
            $offset
        );
        if ($ret === []) {
            $offset = max(0, $offset - ColonyEnum::BUILDMENU_SCROLLOFFSET);
            $ret = $this->buildingRepository->getByColonyAndUserAndBuildMenu(
                (int) $colony->getId(),
                $userId,
                $menu,
                $offset
            );
        }
        $game->setTemplateVar('menu', ['buildings' => $ret]);
        $game->setTemplateVar('menutype', $menu);
        $game->setTemplateVar('scrolloffset', $offset);
        $game->setView(ShowBuildMenuPart::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
