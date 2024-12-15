<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowAlertLevel;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ShowAlertLevel implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ALVL';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(private SpacecraftLoaderInterface $spacecraftLoader) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            true,
            false
        );

        $game->setPageTitle(_('Alarmstufe ändern'));
        $game->setMacroInAjaxWindow('html/ship/showshipalvl.twig');

        $game->setTemplateVar('SHIP', $ship);
    }
}
