<?php

namespace Stu\Module\Control;

use Override;
use request;
use Stu\Lib\AccountNotVerifiedException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Orm\Repository\SessionStringRepositoryInterface;

class AccessCheck implements AccessCheckInterface
{
    public function __construct(
        private readonly SessionStringRepositoryInterface $sessionStringRepository,
        private readonly StuConfigInterface $stuConfig
    ) {}

    #[Override]
    public function checkUserAccess(
        ControllerInterface $controller,
        GameControllerInterface $game
    ): bool {

        if ($controller instanceof NoAccessCheckControllerInterface) {
            return true;
        }

        $hasUser = $game->hasUser();
        if ($hasUser && $game->getUser()->getState() === UserStateEnum::ACCOUNT_VERIFICATION) {
            throw new AccountNotVerifiedException();
        }

        if (!$this->isSessionValid($controller, $hasUser, $game)) {
            return false;
        }

        if (!$controller instanceof AccessCheckControllerInterface) {
            return true;
        }

        $feature = $controller->getFeatureIdentifier();
        if ($hasUser && $this->isFeatureGranted($game->getUser()->getId(), $feature, $game)) {
            return true;
        }

        $game->getInfo()->addInformation('[b][color=#ff2626]Aktion nicht möglich, Spieler ist nicht berechtigt![/color][/b]');

        return false;
    }

    private function isSessionValid(
        ControllerInterface $controller,
        bool $hasUser,
        GameControllerInterface $game
    ): bool {

        if (!$controller instanceof ActionControllerInterface) {
            return true;
        }

        if (!$controller->performSessionCheck()) {
            return true;
        }

        $sessionString = request::indString('sstr');
        if (!$sessionString) {
            return false;
        }

        if (!$hasUser) {
            return false;
        }

        return $this->sessionStringRepository->isValid(
            $sessionString,
            $game->getUser()->getId()
        );
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
