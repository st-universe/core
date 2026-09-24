<?php

namespace Stu\Module\Control;

use request;
use Stu\Lib\AccountNotVerifiedException;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Session\SessionInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SessionStringRepositoryInterface;

class AccessCheck implements AccessCheckInterface
{
    public function __construct(
        private readonly SessionStringRepositoryInterface $sessionStringRepository,
        private readonly StuConfigInterface $stuConfig,
        private readonly GameUserRoleCheckerInterface $gameUserRoleChecker,
        private readonly SessionInterface $session
    ) {}

    #[\Override]
    public function checkUserAccess(
        ControllerInterface $controller,
        InformationInterface $info
    ): bool {

        if ($controller instanceof NoAccessCheckControllerInterface) {
            return true;
        }

        $user = $this->session->getUser();
        if ($user?->getState() === UserStateEnum::ACCOUNT_VERIFICATION) {
            throw new AccountNotVerifiedException();
        }

        if (!$this->isSessionValid($controller, $user)) {
            return false;
        }

        if (!$controller instanceof AccessCheckControllerInterface) {
            return true;
        }

        $feature = $controller->getFeatureIdentifier();
        if ($user !== null && $this->isFeatureGranted($user->getId(), $feature)) {
            return true;
        }

        $info->addInformation('[b][color=#ff2626]Aktion nicht möglich, Spieler ist nicht berechtigt![/color][/b]');

        return false;
    }

    private function isSessionValid(
        ControllerInterface $controller,
        ?User $user
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

        if ($user === null) {
            return false;
        }

        return $this->sessionStringRepository->isValid(
            $sessionString,
            $user->getId()
        );
    }

    #[\Override]
    public function isFeatureGranted(int $userId, AccessGrantedFeatureEnum $feature): bool
    {
        if ($this->gameUserRoleChecker->isAdmin()) {
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
