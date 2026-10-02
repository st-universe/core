<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\RemoveCrewRace;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\PlayerSetting\View\ShowCrewRaceManagement\ShowCrewRaceManagement;
use Stu\Orm\Entity\UserCrewRace;
use Stu\Orm\Repository\UserCrewRaceRepositoryInterface;

final class RemoveCrewRace implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REMOVE_USER_CREW_RACE';

    public function __construct(private readonly UserCrewRaceRepositoryInterface $userCrewRaceRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowCrewRaceManagement::VIEW_IDENTIFIER);

        $user = $context->getUser();
        $crewRaceId = request::postInt('crew_race_id');
        $userCrewRaces = $this->userCrewRaceRepository->getByUserId($user->getId());
        $userCrewRace = null;
        foreach ($userCrewRaces as $entry) {
            if ($entry->getCrewRace()->getId() === $crewRaceId) {
                $userCrewRace = $entry;
                break;
            }
        }
        if (!$userCrewRace instanceof UserCrewRace) {
            $context->getInfo()->addInformation(_('Diese Crew-Rasse ist nicht ausgewählt'));
            return;
        }
        if (count($userCrewRaces) <= 1) {
            $context->getInfo()->addInformation(_('Es muss mindestens eine Crew-Rasse ausgewählt bleiben'));
            return;
        }

        $this->userCrewRaceRepository->delete($userCrewRace);
        $context->getInfo()->addInformation(_('Die Crew-Rasse wurde abgewählt'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
