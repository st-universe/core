<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting;

use Stu\Module\Control\GameController;
use Stu\Module\Game\View\Overview\Overview;
use Stu\Module\PlayerSetting\Action\ActivateVacation\ActivateVacation;
use Stu\Module\PlayerSetting\Action\ChangeAvatar\ChangeAvatar;
use Stu\Module\PlayerSetting\Action\ChangeDescription\ChangeDescription;
use Stu\Module\PlayerSetting\Action\ChangeDescription\ChangeDescriptionRequest;
use Stu\Module\PlayerSetting\Action\ChangeDescription\ChangeDescriptionRequestInterface;
use Stu\Module\PlayerSetting\Action\ChangeEmail\ChangeEmail;
use Stu\Module\PlayerSetting\Action\ChangeEmail\ChangeEmailRequest;
use Stu\Module\PlayerSetting\Action\ChangeEmail\ChangeEmailRequestInterface;
use Stu\Module\PlayerSetting\Action\ChangePassword\ChangePassword;
use Stu\Module\PlayerSetting\Action\ChangePassword\ChangePasswordRequest;
use Stu\Module\PlayerSetting\Action\ChangePassword\ChangePasswordRequestInterface;
use Stu\Module\PlayerSetting\Action\ChangeRgbCode\ChangeRgbCode;
use Stu\Module\PlayerSetting\Action\ChangeSettings\ChangeSettings;
use Stu\Module\PlayerSetting\Action\ChangeSettings\ChangeSettingsRequest;
use Stu\Module\PlayerSetting\Action\ChangeSettings\ChangeSettingsRequestInterface;
use Stu\Module\PlayerSetting\Action\ChangeUserName\ChangeUserName;
use Stu\Module\PlayerSetting\Action\ChangeUserName\ChangeUserNameRequest;
use Stu\Module\PlayerSetting\Action\ChangeUserName\ChangeUserNameRequestInterface;
use Stu\Module\PlayerSetting\Action\DeleteAccount\DeleteAccount;

use function DI\autowire;

return [
    ChangeUserNameRequestInterface::class => autowire(ChangeUserNameRequest::class),
    ChangePasswordRequestInterface::class => autowire(ChangePasswordRequest::class),
    ChangeEmailRequestInterface::class => autowire(ChangeEmailRequest::class),
    ChangeDescriptionRequestInterface::class => autowire(ChangeDescriptionRequest::class),
    ChangeSettingsRequestInterface::class => autowire(ChangeSettingsRequest::class),
    'PLAYER_SETTING_ACTIONS' => [
        ChangeUserName::ACTION_IDENTIFIER => autowire(ChangeUserName::class),
        ChangePassword::ACTION_IDENTIFIER => autowire(ChangePassword::class),
        ChangeEmail::ACTION_IDENTIFIER => autowire(ChangeEmail::class),
        ChangeRgbCode::ACTION_IDENTIFIER => autowire(ChangeRgbCode::class),
        ChangeAvatar::ACTION_IDENTIFIER => autowire(ChangeAvatar::class),
        ChangeDescription::ACTION_IDENTIFIER => autowire(ChangeDescription::class),
        ChangeSettings::ACTION_IDENTIFIER => autowire(ChangeSettings::class),
        ActivateVacation::ACTION_IDENTIFIER => autowire(ActivateVacation::class),
        DeleteAccount::ACTION_IDENTIFIER => autowire(DeleteAccount::class),
    ],
    'PLAYER_SETTING_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
    ]
];
