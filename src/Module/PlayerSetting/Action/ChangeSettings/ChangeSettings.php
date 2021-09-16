<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeSettings;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ChangeSettings implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_SETTINGS';

    private ChangeSettingsRequestInterface $changeSettingsRequest;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        ChangeSettingsRequestInterface $changeSettingsRequest,
        UserRepositoryInterface $userRepository
    ) {
        $this->changeSettingsRequest = $changeSettingsRequest;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $settings = [
            function (UserInterface $user): void {
                $user->setEmailNotification(
                    $this->changeSettingsRequest->getEmailNotification() === 1 ? true : false
                );
            },
            function (UserInterface $user): void {
                $user->setSaveLogin(
                    $this->changeSettingsRequest->getSaveLogin() === 1 ? true : false
                );
            },
            function (UserInterface $user): void {
                $user->setStorageNotification(
                    $this->changeSettingsRequest->getStorageNotification() === 1 ? true : false
                );
            },
            function (UserInterface $user): void {
                $user->setShowOnlineState(
                    $this->changeSettingsRequest->getShowOnlineState() === 1 ? true : false
                );
            },
            function (UserInterface $user): void {
                $user->setFleetFixedDefault(
                    $this->changeSettingsRequest->getFleetsFixedDefault() === 1 ? true : false
                );
            },
        ];
        foreach ($settings as $callable) {
            $callable($user);
        }

        $this->userRepository->save($user);

        $game->addInformation(_('Die Accounteinstellungen wurden aktualisiert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
