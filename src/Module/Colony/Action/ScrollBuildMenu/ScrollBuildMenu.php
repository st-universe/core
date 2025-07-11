<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ScrollBuildMenu;

use Override;
use request;
use Stu\Component\Building\BuildMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\View\ShowBuildMenuPart\ShowBuildMenuPart;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;

final class ScrollBuildMenu implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SCROLL_BUILDMENU';

    public const int BUILDMENU_SCROLLOFFSET = 6;

    public function __construct(private PlanetFieldHostProviderInterface $planetFieldHostProvider, private BuildingRepositoryInterface $buildingRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $host = $this->planetFieldHostProvider->loadHostViaRequestParameters($game->getUser(), false);

        $menu = BuildMenuEnum::from(request::getIntFatal('menu'));
        $offset = request::getInt('offset');
        $fieldType = request::has('fieldtype') ? request::getIntFatal('fieldtype') : null;
        if ($fieldType === 0) {
            $fieldType = null;
        }

        if ($offset < 0) {
            $offset = 0;
        }
        if ($offset % self::BUILDMENU_SCROLLOFFSET != 0) {
            $offset = (int)floor($offset / self::BUILDMENU_SCROLLOFFSET);
        }
        $ret = $this->buildingRepository->getBuildmenuBuildings(
            $host,
            $userId,
            $menu,
            $offset,
            null,
            $fieldType
        );
        if ($ret === []) {
            $offset = max(0, $offset - self::BUILDMENU_SCROLLOFFSET);
            $ret = $this->buildingRepository->getBuildmenuBuildings(
                $host,
                $userId,
                $menu,
                $offset,
                null,
                $fieldType
            );
        }
        $game->setTemplateVar('menu', ['buildings' => $ret, 'name' => $menu->getDescription()]);
        $game->setTemplateVar('menutype', $menu);
        $game->setTemplateVar('scrolloffset', $offset);
        $game->setView(ShowBuildMenuPart::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
