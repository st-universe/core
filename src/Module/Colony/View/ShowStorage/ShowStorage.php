<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowStorage;

use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowStorage implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STORAGE_AJAX';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    private ShowStorageRequestInterface $showStorageRequest;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowStorageRequestInterface $showStorageRequest,
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showStorageRequest = $showStorageRequest;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showStorageRequest->getColonyId(),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $game->showMacro('html/colonymacros.xhtml/colonystorage');
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_SURFACE', $this->colonyLibFactory->createColonySurface($colony));
    }
}
