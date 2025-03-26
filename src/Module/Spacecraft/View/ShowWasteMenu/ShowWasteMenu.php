<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowWasteMenu;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;


final class ShowWasteMenu implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_WASTEMENU';

    /**
     * @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spaceCraftLoader
     */
    public function __construct(
        private SpacecraftLoaderInterface $spaceCraftLoader
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $spacecraft = $this->spaceCraftLoader->getByIdAndUser(request::getIntFatal('id'), $userId);

        if (!$game->getUser()->isNpc()) {
            return;
        }

        $game->setPageTitle(_('Müllverbrennung'));
        if ($game->getViewContext(ViewContextTypeEnum::NO_AJAX) === true) {
            $game->showMacro('html/spacecraft/waste.twig');
        } else {
            $game->setMacroInAjaxWindow('html/spacecraft/waste.twig');
        }

        $game->setTemplateVar('SPACECRAFT', $spacecraft);
    }
}