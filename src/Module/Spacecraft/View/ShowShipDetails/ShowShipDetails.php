<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowShipDetails;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ShowShipDetails implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SPACECRAFTDETAILS';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            true,
            false
        );

        $game->setPageTitle(_('Schiffsinformationen'));
        $game->setMacroInAjaxWindow('html/ship/shipDetails.twig');

        $game->setTemplateVar('WRAPPER', $wrapper);
    }
}
