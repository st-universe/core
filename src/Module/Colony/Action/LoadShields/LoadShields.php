<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\LoadShields;

use request;

use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class LoadShields implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LOAD_SHIELDS';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyRepositoryInterface $colonyRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyRepository = $colonyRepository;
        $this->planetFieldRepository = $planetFieldRepository;
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

        $maxShields = $this->planetFieldRepository->getMaxShieldsOfHost($colony);

        if ($load > $maxShields - $colony->getShields()) {
            $load = $maxShields - $colony->getShields();
        }

        if ($load < 1) {
            return;
        }

        $colony->setEps($colony->getEps() - (int) ceil($load / ColonyEnum::SHIELDS_PER_EPS));
        $colony->setShields($colony->getShields() + $load);

        $this->colonyRepository->save($colony);
        $game->addInformation(sprintf(_('Die Schilde wurden um %d Punkte geladen'), $load));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
