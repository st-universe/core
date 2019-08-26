<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowColony;

use ColonyMenu;
use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowColony implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONY';

    private $colonyLoader;

    private $colonyGuiHelper;

    private $showColonyRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowColonyRequestInterface $showColonyRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showColonyRequest = $showColonyRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showColonyRequest->getColonyId(),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $menuId = $game->getViewContext()['COLONY_MENU'] ?? MENU_INFO;

        $game->appendNavigationPart(
            'colony.php',
            _('Kolonien')
        );
        $game->appendNavigationPart(
            sprintf('?%s=1&id=%d', static::VIEW_IDENTIFIER, $colony->getId()),
            $colony->getNameWithoutMarkup()
        );
        $game->setTemplateFile('html/colony.xhtml');
        $game->setPagetitle(sprintf(_('Kolonie: %s'), $colony->getNameWithoutMarkup()));

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar(
            'SELECTED_COLONY_MENU',
            $this->colonyGuiHelper->getColonyMenu($menuId)
        );
        $game->setTemplateVar(
            'COLONY_MENU_SELECTOR',
            new ColonyMenu($menuId)
        );
    }
}
