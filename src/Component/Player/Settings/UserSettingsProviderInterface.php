<?php

declare(strict_types=1);

namespace Stu\Component\Player\Settings;

use Stu\Component\Game\ModuleEnum;
use Stu\Component\Player\UserCssClassEnum;
use Stu\Component\Player\UserRpgBehaviorEnum;
use Stu\Orm\Entity\User;

interface UserSettingsProviderInterface
{
    // STRING
    public function getRgbCode(User $user): string;
    public function getAvatar(User $user): string;

    // ENUM
    public function getDefaultView(User $user): ModuleEnum;
    public function getRpgBehavior(User $user): UserRpgBehaviorEnum;
    public function getCss(User $user): UserCssClassEnum;

    // BOOL
    public function isEmailNotification(User $user): bool;
    public function isStorageNotification(User $user): bool;
    public function isShowOnlineState(User $user): bool;
    public function isShowPmReadReceipt(User $user): bool;
    public function isSaveLogin(User $user): bool;
    public function getFleetFixedDefault(User $user): bool;
    public function getWarpsplitAutoCarryoverDefault(User $user): bool;
    public function isShowPirateHistoryEntrys(User $user): bool;
    public function isInboxMessengerStyle(User $user): bool;
}
