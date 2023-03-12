<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowTorpedoFab;

use Stu\Module\Colony\Lib\ColonyMenu;
use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class ShowTorpedoFab implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TORPEDO_FAB';

    private ColonyLoaderInterface $colonyLoader;

    private ShowTorpedoFabRequestInterface $showTorpedoFabRequest;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowTorpedoFabRequestInterface $showTorpedoFabRequest,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showTorpedoFabRequest = $showTorpedoFabRequest;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showTorpedoFabRequest->getColonyId(),
            $userId,
            false
        );

        $game->showMacro('html/colonymacros.xhtml/cm_torpedo_fab');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(ColonyEnum::MENU_TORPEDOFAB));
        $game->setTemplateVar('BUILDABLE_TORPEDO_TYPES', $this->torpedoTypeRepository->getForUser($userId));
    }
}
