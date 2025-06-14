<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowLSSFilter;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Component\Spacecraft\SpacecraftLssModeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ShowLSSFilter implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_LSS_FILTER';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader*/
    public function __construct(private SpacecraftLoaderInterface $spacecraftLoader) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $game->setPageTitle(_('LSS Filter'));
        $game->setMacroInAjaxWindow('html/spacecraft/lssFilter.twig');
        $game->setTemplateVar('WRAPPER', $wrapper);
        $game->setTemplateVar('LSS_MODES', SpacecraftLssModeEnum::cases());
    }
}
