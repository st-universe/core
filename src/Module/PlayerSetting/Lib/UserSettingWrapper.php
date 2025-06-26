<?php

namespace Stu\Module\PlayerSetting\Lib;

use RuntimeException;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Component\Player\UserCssClassEnum;
use Stu\Component\Player\UserRpgBehaviorEnum;
use Stu\Orm\Entity\User;

class UserSettingWrapper
{
    public function __construct(
        private readonly User $user,
        private readonly UserSettingEnum $type,
        private readonly bool $isAdmin,
        private readonly UserSettingsProviderInterface $userSettingsProvider
    ) {}

    public function getType(): UserSettingEnum
    {
        return $this->type;
    }

    public function getParameterName(): string
    {
        return $this->type->value;
    }

    public function getCurrentValue(): mixed
    {
        return $this->type->getUserValue($this->user, $this->userSettingsProvider);
    }

    /** @return array<mixed> */
    public function getPossibleValues(): array
    {
        return match ($this->type) {
            UserSettingEnum::CSS_COLOR_SHEET => UserCssClassEnum::cases(),
            UserSettingEnum::RPG_BEHAVIOR => UserRpgBehaviorEnum::cases(),
            UserSettingEnum::DEFAULT_VIEW => $this->getFilteredViews($this->user, $this->isAdmin),
            default => throw new RuntimeException(sprintf('%s is not an enum', $this->type->name))
        };
    }

    public function getEnctype(): ?string
    {
        return match ($this->type) {
            UserSettingEnum::AVATAR => "multipart/form-data",
            default => null
        };
    }

    /** @return array<ModuleEnum> */
    private function getFilteredViews(User $user, bool $isAdmin): array
    {
        return array_filter(ModuleEnum::cases(), function (ModuleEnum $case) use ($user, $isAdmin): bool {

            if (in_array($case, [ModuleEnum::GAME, ModuleEnum::INDEX, ModuleEnum::NOTES])) {
                return false;
            }

            if ($case === ModuleEnum::ADMIN && !$isAdmin) {
                return false;
            }
            return !($case === ModuleEnum::NPC && !$user->isNpc());
        });
    }
}
