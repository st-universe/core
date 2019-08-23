<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting;

use Stu\Control\GameController;
use Stu\Control\IntermediateController;
use Stu\Lib\SessionInterface;
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
use Stu\Module\PlayerSetting\Action\ChangeSettings\ChangeSettings;
use Stu\Module\PlayerSetting\Action\ChangeSettings\ChangeSettingsRequest;
use Stu\Module\PlayerSetting\Action\ChangeSettings\ChangeSettingsRequestInterface;
use Stu\Module\PlayerSetting\Action\ChangeUserName\ChangeUserName;
use Stu\Module\PlayerSetting\Action\ChangeUserName\ChangeUserNameRequest;
use Stu\Module\PlayerSetting\Action\ChangeUserName\ChangeUserNameRequestInterface;
use Stu\Module\PlayerSetting\View\Overview\Overview;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    ChangeUserNameRequestInterface::class => autowire(ChangeUserNameRequest::class),
    ChangePasswordRequestInterface::class => autowire(ChangePasswordRequest::class),
    ChangeEmailRequestInterface::class => autowire(ChangeEmailRequest::class),
    ChangeDescriptionRequestInterface::class => autowire(ChangeDescriptionRequest::class),
    ChangeSettingsRequestInterface::class => autowire(ChangeSettingsRequest::class),
    IntermediateController::TYPE_PLAYER_SETTING => create(IntermediateController::class)
        ->constructor(
            get(SessionInterface::class),
            get(SessionStringRepositoryInterface::class),
            [
                ChangeUserName::ACTION_IDENTIFIER => autowire(ChangeUserName::class),
                ChangePassword::ACTION_IDENTIFIER => autowire(ChangePassword::class),
                ChangeEmail::ACTION_IDENTIFIER => autowire(ChangeEmail::class),
                ChangeAvatar::ACTION_IDENTIFIER => autowire(ChangeAvatar::class),
                ChangeDescription::ACTION_IDENTIFIER => autowire(ChangeDescription::class),
                ChangeSettings::ACTION_IDENTIFIER => autowire(ChangeSettings::class),
            ],
            [
                GameController::DEFAULT_VIEW => autowire(Overview::class),
            ]
        ),
];