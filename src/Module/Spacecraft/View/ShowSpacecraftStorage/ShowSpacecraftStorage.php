<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSpacecraftStorage;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ShowSpacecraftStorage implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SPACECRAFTSTORAGE';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $spacecraft = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            true,
            false
        );

        $game->setPageTitle(sprintf('Fracht der %s', $spacecraft->getName()));
        $game->setMacroInAjaxWindow('html/spacecraft/spacecraftStorage.twig');

        $game->setTemplateVar('SPACECRAFT', $spacecraft);
    }
}
