<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DenyImmigration;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class DenyImmigration implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_PERMIT_IMMIGRATION';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ColonyRepositoryInterface $colonyRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER);
        $game->setViewContext(ViewContextTypeEnum::COLONY_MENU, ColonyMenuEnum::MENU_OPTION);

        $colony->getChangeable()->setImmigrationState(false);

        $this->colonyRepository->save($colony);

        $game->getInfo()->addInformation(_('Die Einwanderung wurde verboten'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
