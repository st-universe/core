<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeTorpedoType;

use Override;
use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class ChangeTorpedoType implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_TORPS';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ColonyRepositoryInterface $colonyRepository, private ChangeTorpedoTypeRequestInterface $changeTorpedoTypeRequest, private TorpedoTypeRepositoryInterface $torpedoTypeRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER);
        $game->setViewContext(ViewContextTypeEnum::COLONY_MENU, ColonyMenuEnum::MENU_INFO);

        $torpedoId = $this->changeTorpedoTypeRequest->getTorpedoId();

        if ($torpedoId !== 0) {
            $availableTorpedos = $this->torpedoTypeRepository->getForUser($game->getUser()->getId());
            if (!array_key_exists($torpedoId, $availableTorpedos)) {
                $game->getInfo()->addInformation(_('Unerlaubter Torpedo-Typ'));
                return;
            }

            $colony->getChangeable()->setTorpedo($availableTorpedos[$torpedoId]);
        } else {
            $colony->getChangeable()->setTorpedo(null);
        }
        $this->colonyRepository->save($colony);

        $game->getInfo()->addInformation(_('Die Torpedo-Sorte wurde geändert'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
