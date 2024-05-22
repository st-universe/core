<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeTorpedoType;

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
    public const ACTION_IDENTIFIER = 'B_CHANGE_TORPS';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyRepositoryInterface $colonyRepository;

    private ChangeTorpedoTypeRequestInterface $changeTorpedoTypeRequest;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyRepositoryInterface $colonyRepository,
        ChangeTorpedoTypeRequestInterface $changeTorpedoTypeRequest,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyRepository = $colonyRepository;
        $this->changeTorpedoTypeRequest = $changeTorpedoTypeRequest;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
    }

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
                $game->addInformation(_('Unerlaubter Torpedo-Typ'));
                return;
            }

            $colony->setTorpedo($availableTorpedos[$torpedoId]);
        } else {
            $colony->setTorpedo(null);
        }
        $this->colonyRepository->save($colony);

        $game->addInformation(_('Die Torpedo-Sorte wurde geändert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
