<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeSettings;

use request;
use Stu\Component\Crew\CrewRaceUsageEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\ChangeUserSettingInterface;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;
use Stu\Orm\Repository\UserCrewRaceRepositoryInterface;

final class ChangeSettings implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_SETTINGS';

    public function __construct(
        private ChangeUserSettingInterface $changeUserSetting,
        private UserCrewRaceRepositoryInterface $userCrewRaceRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        foreach (UserSettingEnum::cases() as $setting) {
            if ($setting->isDistinctSetting()) {
                continue;
            }

            if (!request::has($setting->value)) {
                $this->changeUserSetting->reset($user, $setting);
            } else {

                $value = request::postStringFatal($setting->value);
                if ($setting === UserSettingEnum::CREW_RACE_USAGE) {
                    $usage = CrewRaceUsageEnum::tryFrom($value);
                    if (
                        $usage === null
                        || ($usage !== CrewRaceUsageEnum::STANDARD && !$this->userCrewRaceRepository->hasAnyForUserId($user->getId()))
                    ) {
                        $game->getInfo()->addInformation(_('Freigeschaltete Crew-Rassen stehen noch nicht zur Verfügung'));
                        $value = CrewRaceUsageEnum::STANDARD->value;
                    }
                }

                $this->changeUserSetting->change(
                    $user,
                    $setting,
                    $value
                );
            }
        }

        $game->getInfo()->addInformation(_('Die Accounteinstellungen wurden aktualisiert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
