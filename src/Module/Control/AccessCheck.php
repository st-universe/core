<?php

namespace Stu\Module\Control;

use Override;
use Stu\Config\Init;
use Stu\Module\Config\StuConfigInterface;

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

        if (!$controller instanceof AccessCheckControllerInterface) {
            return true;
        }

        $feature = $controller->getFeatureIdentifier();
        if ($this->isFeatureGranted($game->getUser()->getId(), $feature)) {
            return true;
        }

        $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist nicht berechtigt![/color][/b]'));

        return false;
    }

    #[Override]
    public function isFeatureGranted(int $userId, AccessGrantedFeatureEnum $feature): bool
    {
        if (Init::getContainer()->get(GameControllerInterface::class)->isAdmin()) {
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
