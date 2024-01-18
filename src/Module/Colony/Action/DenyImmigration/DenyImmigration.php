<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DenyImmigration;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class DenyImmigration implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_PERMIT_IMMIGRATION';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyRepositoryInterface $colonyRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyRepository = $colonyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER, ['COLONY_MENU' => ColonyMenuEnum::MENU_OPTION]);

        $colony->setImmigrationState(false);

        $this->colonyRepository->save($colony);

        $game->addInformation(_('Die Einwanderung wurde verboten'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
