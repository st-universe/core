<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\LoadShields;

use Override;
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
    public const string ACTION_IDENTIFIER = 'B_LOAD_SHIELDS';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private PlanetFieldRepositoryInterface $planetFieldRepository, private ColonyRepositoryInterface $colonyRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
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

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
