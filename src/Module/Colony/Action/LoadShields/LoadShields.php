<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\LoadShields;

use request;

use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class LoadShields implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LOAD_SHIELDS';

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
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $load = request::postIntFatal('load');

        if ($colony->getEps() * ColonyEnum::SHIELDS_PER_EPS < $load) {
            $load = $colony->getEps() * ColonyEnum::SHIELDS_PER_EPS;
        }

        if ($load > $colony->getMaxShields() - $colony->getShields()) {
            $load = $colony->getMaxShields() - $colony->getShields();
        }

        if ($load < 1) {
            return;
        }

        $colony->setEps($colony->getEps() - (int) ceil($load / ColonyEnum::SHIELDS_PER_EPS));
        $colony->setShields($colony->getShields() + (int) $load);

        $this->colonyRepository->save($colony);
        $game->addInformation(sprintf(_('Die Schilde wurden um %d Punkte geladen'), $load));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
