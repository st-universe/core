<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\View\ShowCrewRaceManagement;

use request;
use Stu\Component\Crew\CrewRaceInput;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

final class ShowCrewRaceManagement implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_CREW_RACE_MANAGEMENT';

    public function __construct(
        private readonly CrewRaceRepositoryInterface $crewRaceRepository,
        private readonly FactionRepositoryInterface $factionRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $ownCrewRaces = $this->crewRaceRepository->getByCreatorUserId($user->getId());
        $ownFactionId = $user->getFactionId();

        $game->setViewTemplate('html/user/crewRaceManagement.twig');
        $game->setPageTitle(_('Crew-Rassen verwalten'));
        $game->appendNavigationPart('options.php', _('Optionen'));
        $game->appendNavigationPart('?SHOW_CREW_RACE_MANAGEMENT=1', _('Crew-Rassen'));
        $game->setTemplateVar('OWN_CREW_RACES', $ownCrewRaces);
        $game->setTemplateVar('PLAYABLE_FACTIONS', $this->factionRepository->getByChooseable(true));
        $game->setTemplateVar('OWN_FACTION_ID', $ownFactionId);
        $game->setTemplateVar('CAN_CREATE_CREW_RACE', $game->isAdmin() || count($ownCrewRaces) < 3);
        $game->setTemplateVar('CREW_RACE_CREATION_REMAINING', $game->isAdmin() ? null : max(0, 3 - count($ownCrewRaces)));
        $formName = request::postString('crew_race_name') ?: '';
        $game->setTemplateVar('FORM_CREW_RACE_NAME', $formName);
        $game->setTemplateVar('FORM_CREW_RACE_DEFINE', request::postString('crew_race_define') ?: CrewRaceInput::normalizeDefine($formName));
        $game->setTemplateVar('FORM_CREW_RACE_MALE_RATIO', request::postString('crew_race_male_ratio') ?: '50');
        $game->setTemplateVar('FORM_CREW_RACE_CHANCE', request::postString('crew_race_chance') ?: '25');
        $game->setTemplateVar('FORM_CREW_RACE_SHARED', request::postString('crew_race_shared') === '1');
        $game->setTemplateVar('FORM_CREW_RACE_FACTION_IDS', array_map('intval', request::postArray('crew_race_factions')));
    }
}
