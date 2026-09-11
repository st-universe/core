<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\SelectCrewRace;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\View\ShowCrewRaceManagement\ShowCrewRaceManagement;
use Stu\Orm\Entity\CrewRace;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\UserCrewRaceRepositoryInterface;

final class SelectCrewRace implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SELECT_USER_CREW_RACE';

    public function __construct(
        private readonly CrewRaceRepositoryInterface $crewRaceRepository,
        private readonly UserCrewRaceRepositoryInterface $userCrewRaceRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowCrewRaceManagement::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $crewRaceId = request::postInt('crew_race_id');
        $crewRace = $this->crewRaceRepository->find($crewRaceId);
        $selectableCrewRaceIds = array_map(
            static fn (CrewRace $race): int => $race->getId(),
            $this->crewRaceRepository->getSelectableForUser($user->getId(), $user->getFactionId())
        );
        if ($crewRace === null || !in_array($crewRaceId, $selectableCrewRaceIds, true)) {
            $game->getInfo()->addInformation(_('Diese Crew-Rasse kann nicht ausgewählt werden'));
            return;
        }
        if ($this->userCrewRaceRepository->exists($crewRaceId, $user->getId())) {
            $game->getInfo()->addInformation(_('Diese Crew-Rasse ist bereits ausgewählt'));
            return;
        }

        $this->userCrewRaceRepository->save(
            $this->userCrewRaceRepository->prototype()
                ->setCrewRace($crewRace)
                ->setUserId($user->getId())
                ->setChance($crewRace->getChance())
        );
        $game->getInfo()->addInformation(_('Die Crew-Rasse wurde ausgewählt'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
