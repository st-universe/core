<?php

declare(strict_types=1);

namespace Stu\Component\Player\Settings;

use Stu\Component\Game\ModuleEnum;
use Stu\Component\Player\UserCssClassEnum;
use Stu\Component\Player\UserRpgBehaviorEnum;
use Stu\Orm\Entity\UserInterface;

interface UserSettingsProviderInterface
{
    // STRING
    public function getRgbCode(UserInterface $user): string;
    public function getAvatar(UserInterface $user): string;

    // ENUM
    public function getDefaultView(UserInterface $user): ModuleEnum;
    public function getRpgBehavior(UserInterface $user): UserRpgBehaviorEnum;
    public function getCss(UserInterface $user): UserCssClassEnum;

    // BOOL
    public function isEmailNotification(UserInterface $user): bool;
    public function isStorageNotification(UserInterface $user): bool;
    public function isShowOnlineState(UserInterface $user): bool;
    public function isShowPmReadReceipt(UserInterface $user): bool;
    public function isSaveLogin(UserInterface $user): bool;
    public function getFleetFixedDefault(UserInterface $user): bool;
    public function getWarpsplitAutoCarryoverDefault(UserInterface $user): bool;
    public function isShowPirateHistoryEntrys(UserInterface $user): bool;
    public function isInboxMessengerStyle(UserInterface $user): bool;
}
