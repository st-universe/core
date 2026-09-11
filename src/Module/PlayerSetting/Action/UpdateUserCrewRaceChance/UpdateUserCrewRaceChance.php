<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\UpdateUserCrewRaceChance;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\View\ShowCrewRaceManagement\ShowCrewRaceManagement;
use Stu\Orm\Entity\UserCrewRace;
use Stu\Orm\Repository\UserCrewRaceRepositoryInterface;

final class UpdateUserCrewRaceChance implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_UPDATE_USER_CREW_RACE_CHANCE';

    public function __construct(private readonly UserCrewRaceRepositoryInterface $userCrewRaceRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowCrewRaceManagement::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $crewRaceId = request::postInt('crew_race_id');
        $chanceValue = request::postString('chance');
        $chance = $chanceValue === false ? 0 : filter_var($chanceValue, FILTER_VALIDATE_INT);
        $chance = $chance === false ? 0 : $chance;
        $userCrewRace = null;
        foreach ($this->userCrewRaceRepository->getByUserId($user->getId()) as $entry) {
            if ($entry->getCrewRace()->getId() === $crewRaceId) {
                $userCrewRace = $entry;
                break;
            }
        }
        if (!$userCrewRace instanceof UserCrewRace) {
            $game->getInfo()->addInformation(_('Diese Crew-Rasse ist nicht ausgewählt'));
            return;
        }
        if ($chance < 1 || $chance > 100) {
            $game->getInfo()->addInformation(_('Die Zufallsrate muss eine Zahl zwischen 1 und 100 sein'));
            return;
        }

        $userCrewRace->setChance($chance);
        $this->userCrewRaceRepository->save($userCrewRace);
        $game->getInfo()->addInformation(_('Die Zufallsrate wurde gespeichert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
