<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowTorpedoFab;

use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
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

        $game->showMacro(ColonyMenuEnum::MENU_TORPEDOFAB->getTemplate());
        $game->setTemplateVar('CURRENT_MENU', ColonyMenuEnum::MENU_TORPEDOFAB);

        $game->setTemplateVar('HOST', $colony);
        $game->setTemplateVar('BUILDABLE_TORPEDO_TYPES', $this->torpedoTypeRepository->getForUser($userId));
    }
}
