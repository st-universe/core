<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSelfDestruct;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ShowSelfDestruct implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SELFDESTRUCT_AJAX';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(private SpacecraftLoaderInterface $spacecraftLoader) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $code = substr(md5($ship->getName()), 0, 6);

        $game->setPageTitle(_('Selbstzerstörung'));
        $game->setMacroInAjaxWindow('html/ship/selfdestruct.twig');

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('SELF_DESTRUCT_CODE', $code);
    }
}
