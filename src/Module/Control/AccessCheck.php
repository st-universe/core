<?php

namespace Stu\Module\Control;

use Override;
use Stu\Lib\AccountNotVerifiedException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;

class AccessCheck implements AccessCheckInterface
{
    public function __construct(
        private StuConfigInterface $stuConfig
    ) {}

    #[Override]
    public function checkUserAccess(
        ControllerInterface $controller,
        GameControllerInterface $game
    ): bool {

        if ($controller instanceof NoAccessCheckControllerInterface) {
            return true;
        }

        if ($game->hasUser() && $game->getUser()->getState() === UserEnum::USER_STATE_ACCOUNT_VERIFICATION) {
            throw new AccountNotVerifiedException();
        }

        if (!$controller instanceof AccessCheckControllerInterface) {
            return true;
        }

        $feature = $controller->getFeatureIdentifier();
        if ($this->isFeatureGranted($game->getUser()->getId(), $feature, $game)) {
            return true;
        }

        $game->addInformation('[b][color=#ff2626]Aktion nicht möglich, Spieler ist nicht berechtigt![/color][/b]');

        return false;
    }

    #[Override]
    public function isFeatureGranted(int $userId, AccessGrantedFeatureEnum $feature, GameControllerInterface $game): bool
    {
        if ($game->isAdmin()) {
            return true;
        }

        $grantedFeatures = $this->stuConfig->getGameSettings()->getGrantedFeatures();
        foreach ($grantedFeatures as $entry) {
            if (
                $entry['feature'] === $feature->name
                && in_array($userId, $entry['userIds'])
            ) {
                return true;
            }
        }

        return false;
    }
}