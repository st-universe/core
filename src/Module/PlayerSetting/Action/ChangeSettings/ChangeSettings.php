<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeSettings;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use UserData;

final class ChangeSettings implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_SETTINGS';

    private $changeSettingsRequest;

    public function __construct(
        ChangeSettingsRequestInterface $changeSettingsRequest
    ) {
        $this->changeSettingsRequest = $changeSettingsRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $settings = [
            function (UserData $user): void {
                $user->setEmailNotification(
                    $this->changeSettingsRequest->getEmailNotification() === 1 ? 1 : 0
                );
            },
            function (UserData $user): void {
                $user->setSaveLogin(
                    $this->changeSettingsRequest->getSaveLogin() === 1 ? 1 : 0
                );
            },
            function (UserData $user): void {
                $user->setStorageNotification(
                    $this->changeSettingsRequest->getStorageNotification() === 1 ? 1 : 0
                );
            },
            function (UserData $user): void {
                $user->setShowOnlineState(
                    $this->changeSettingsRequest->getShowOnlineState() === 1 ? 1 : 0
                );
            },
        ];
        foreach ($settings as $callable) {
            $callable($user);
        }

        $user->save();

        $game->addInformation(_('Die Accounteinstellungen wurden aktualisiert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
