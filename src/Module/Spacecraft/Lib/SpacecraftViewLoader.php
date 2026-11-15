<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Exception\AccessViolationException;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Game\Lib\View\ViewComponentLoaderInterface;

final class SpacecraftViewLoader
{
    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(private SpacecraftLoaderInterface $spacecraftLoader, private ViewComponentLoaderInterface $views) {}

    public function getWrapper(int $spacecraftId, int $userId, ViewControllerContext $game): ?SpacecraftWrapperInterface
    {
        try {
            return $this->spacecraftLoader->getWrapperByIdAndUser($spacecraftId, $userId, true, false);
        } catch (AccessViolationException) {
            $game->getInfo()->addInformation('Dieses Schiff ist nicht mehr verfügbar oder gehört nicht zu deinem Account');
            $module = $game->getModule();
            $this->views->registerViewComponents($module, $game);
            $game->setPageTitle($module->getTitle());
            $game->setViewTemplate($module->getTemplate());
            return null;
        }
    }
}
