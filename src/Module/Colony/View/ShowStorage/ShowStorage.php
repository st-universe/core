<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowStorage;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowStorage implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STORAGE_AJAX';

    private $colonyLoader;

    private $colonyGuiHelper;

    private $showStorageRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowStorageRequestInterface $showStorageRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showStorageRequest = $showStorageRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showStorageRequest->getColonyId(),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setAjaxMacro('html/colonymacros.xhtml/colonystorage');
        $game->setTemplateVar('COLONY', $colony);
    }
}
