<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\LoadShields;

use Override;
use request;

use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class LoadShields implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_LOAD_SHIELDS';

    private const int SHIELDS_PER_EPS = 10;

    public function __construct(
        private readonly ColonyLoaderInterface $colonyLoader,
        private readonly PlanetFieldRepositoryInterface $planetFieldRepository,
        private readonly ColonyRepositoryInterface $colonyRepository
    ) {}

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
        $changeable = $colony->getChangeable();

        if ($changeable->getEps() * self::SHIELDS_PER_EPS < $load) {
            $load = $changeable->getEps() * self::SHIELDS_PER_EPS;
        }

        $maxShields = $this->planetFieldRepository->getMaxShieldsOfHost($colony);

        if ($load > $maxShields - $changeable->getShields()) {
            $load = $maxShields - $changeable->getShields();
        }

        if ($load < 1) {
            return;
        }

        $changeable->lowerEps((int) ceil($load / self::SHIELDS_PER_EPS));
        $changeable->setShields($changeable->getShields() + $load);

        $this->colonyRepository->save($colony);
        $game->getInfo()->addInformation(sprintf(_('Die Schilde wurden um %d Punkte geladen'), $load));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
