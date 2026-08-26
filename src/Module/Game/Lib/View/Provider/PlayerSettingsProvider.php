<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Component\Faction\FactionEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;
use Stu\Module\PlayerSetting\Lib\UserSettingWrapper;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;

final class PlayerSettingsProvider implements ViewComponentProviderInterface
{
    public function __construct(
        private readonly UserSettingsProviderInterface $userSettingsProvider,
        private readonly UserCrewRankRepositoryInterface $userCrewRankRepository
    ) {}

    #[\Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $game->appendNavigationPart(
            'options.php',
            _('Optionen')
        );

        $game->setTemplateVar('DISTINCT_SETTING_WRAPPERS', array_map(fn (UserSettingEnum $type): UserSettingWrapper => new UserSettingWrapper($user, $type, $game->isAdmin(), $this->userSettingsProvider), UserSettingEnum::getDistinct()));
        $game->setTemplateVar('SETTING_WRAPPERS', array_map(fn (UserSettingEnum $type): UserSettingWrapper => new UserSettingWrapper($user, $type, $game->isAdmin(), $this->userSettingsProvider), UserSettingEnum::getNonDistinct()));

        $game->setTemplateVar('REAL_USER', $user);
        $game->setTemplateVar('CREW_RANKS', CrewSkillLevelEnum::cases());
        $customCrewRankNames = [];
        foreach (CrewSkillLevelEnum::cases() as $rank) {
            $customCrewRankNames[$rank->value] = $this->userCrewRankRepository->getCustomRankName($user, $rank);
        }
        $game->setTemplateVar('CUSTOM_CREW_RANK_NAMES', $customCrewRankNames);
        $game->setTemplateVar('FEDERATION_ID', FactionEnum::FACTION_FEDERATION->value);
        $game->setTemplateVar('SHOW_FACTION_CREW_RANKS', $user->getFactionId() !== FactionEnum::FACTION_FEDERATION->value);
    }
}
