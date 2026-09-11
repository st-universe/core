<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\View\ShowCrewRaceManagement;

use request;
use Stu\Component\Crew\CrewRaceInput;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\UserCrewRaceRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShowCrewRaceManagement implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_CREW_RACE_MANAGEMENT';

    public function __construct(
        private readonly CrewRaceRepositoryInterface $crewRaceRepository,
        private readonly FactionRepositoryInterface $factionRepository,
        private readonly UserCrewRaceRepositoryInterface $userCrewRaceRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $ownCrewRaces = $this->crewRaceRepository->getByCreatorUserId($user->getId());
        $ownFactionId = $user->getFactionId();
        $userCrewRaces = [];
        foreach ($this->userCrewRaceRepository->getByUserId($user->getId()) as $userCrewRace) {
            $userCrewRaces[$userCrewRace->getCrewRace()->getId()] = $userCrewRace;
        }
        $selectableCrewRaces = $this->crewRaceRepository->getSelectableForUser($user->getId(), $ownFactionId);
        foreach ($userCrewRaces as $userCrewRace) {
            if (!in_array($userCrewRace->getCrewRace(), $selectableCrewRaces, true)) {
                $selectableCrewRaces[] = $userCrewRace->getCrewRace();
            }
        }
        usort(
            $selectableCrewRaces,
            static fn ($a, $b): int => [$a->isCivil() ? 1 : 0, $a->getDescription()] <=> [$b->isCivil() ? 1 : 0, $b->getDescription()]
        );
        $crewRaceOwnerNames = [];
        foreach ($selectableCrewRaces as $crewRace) {
            $creatorUserId = $crewRace->getCreatorUserId();
            if ($creatorUserId !== null) {
                $crewRaceOwnerNames[$crewRace->getId()] = $this->userRepository->find($creatorUserId)?->getName() ?? _('Niemand');
            }
        }

        $game->setViewTemplate('html/user/crewRaceManagement.twig');
        $game->setPageTitle(_('Crew-Rassen verwalten'));
        $game->appendNavigationPart('options.php', _('Optionen'));
        $game->appendNavigationPart('?SHOW_CREW_RACE_MANAGEMENT=1', _('Crew-Rassen'));
        $game->setTemplateVar('OWN_CREW_RACES', $ownCrewRaces);
        $game->setTemplateVar('SELECTABLE_CREW_RACES', $selectableCrewRaces);
        $game->setTemplateVar('USER_CREW_RACES', $userCrewRaces);
        $game->setTemplateVar('CREW_RACE_OWNER_NAMES', $crewRaceOwnerNames);
        $game->setTemplateVar('PLAYABLE_FACTIONS', $this->factionRepository->getByChooseable(true));
        $game->setTemplateVar('OWN_FACTION_ID', $ownFactionId);
        $game->setTemplateVar('CAN_CREATE_CREW_RACE', $game->isAdmin() || count($ownCrewRaces) < 3);
        $game->setTemplateVar('CREW_RACE_CREATION_REMAINING', $game->isAdmin() ? null : max(0, 3 - count($ownCrewRaces)));
        $editingRace = null;
        $editingId = request::indInt('crew_race_id');
        foreach ($ownCrewRaces as $crewRace) {
            if ($crewRace->getId() === $editingId && $crewRace->isRejected()) {
                $editingRace = $crewRace;
                break;
            }
        }
        $game->setTemplateVar('EDIT_CREW_RACE', $editingRace);
        $submitted = (request::postString('B_CREATE_CREW_RACE') !== false
            || request::postString('B_RESUBMIT_CREW_RACE') !== false)
            && ($editingId === 0 || $editingRace !== null);
        $formName = $submitted ? (string)request::postString('crew_race_name') : ($editingRace?->getDescription() ?? '');
        $game->setTemplateVar('FORM_CREW_RACE_NAME', $formName);
        $game->setTemplateVar('FORM_CREW_RACE_DEFINE', $submitted
            ? (string)request::postString('crew_race_define')
            : ($editingRace?->getGfxPath() ?? CrewRaceInput::normalizeDefine($formName)));
        $game->setTemplateVar('FORM_CREW_RACE_MALE_RATIO', $submitted ? request::postString('crew_race_male_ratio') : ($editingRace?->getMaleRatio() ?? 50));
        $game->setTemplateVar('FORM_CREW_RACE_CHANCE', $submitted ? request::postString('crew_race_chance') : ($editingRace?->getChance() ?? 25));
        $game->setTemplateVar('FORM_CREW_RACE_SHARED', $submitted ? request::postString('crew_race_shared') === '1' : ($editingRace?->isShared() ?? false));
        $game->setTemplateVar('FORM_CREW_RACE_CIVIL', $submitted ? request::postString('crew_race_civil') === '1' : ($editingRace?->isCivil() ?? true));
        $game->setTemplateVar('FORM_CREW_RACE_FACTION_IDS', $submitted
            ? array_map('intval', request::postArray('crew_race_factions'))
            : ($editingRace?->getFactionIds() ?? []));
    }
}
