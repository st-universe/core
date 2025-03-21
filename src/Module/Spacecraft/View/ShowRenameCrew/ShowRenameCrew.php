<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowRenameCrew;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ShowRenameCrew implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_RENAME_CREW';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(private SpacecraftLoaderInterface $spacecraftLoader) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $game->showMacro('html/ship/crew/crewSlot.twig');

        $game->setTemplateVar('SHIP', $ship);
    }
}
