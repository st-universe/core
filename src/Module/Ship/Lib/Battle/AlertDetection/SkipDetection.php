<?php

namespace Stu\Module\Ship\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Module\Control\StuTime;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;

class SkipDetection implements SkipDetectionInterface
{
    public function __construct(
        private PlayerRelationDeterminatorInterface $playerRelationDeterminator,
        private StuTime $stuTime
    ) {
    }

    #[Override]
    public function isSkipped(
        ShipInterface $incomingShip,
        ShipInterface $alertedShip,
        ?ShipInterface $tractoringShip,
        Collection $usersToInformAboutTrojanHorse
    ): bool {
        $alertUser = $alertedShip->getUser();
        $incomingShipUser = $incomingShip->getUser();

        //alert yellow only attacks if incoming is foe
        if (
            $alertedShip->getAlertState() === ShipAlertStateEnum::ALERT_YELLOW
            && !$this->playerRelationDeterminator->isEnemy($alertUser, $incomingShipUser)
        ) {
            return true;
        }

        //now ALERT-MODE: RED or YELLOW+ENEMY

        //ships of friends from tractoring ship dont attack
        if (
            $tractoringShip !== null
            && $this->playerRelationDeterminator->isFriend($alertUser, $tractoringShip->getUser())
        ) {
            if (
                !$usersToInformAboutTrojanHorse->contains($alertUser)
                && !$this->playerRelationDeterminator->isFriend($alertUser, $incomingShipUser)
            ) {
                $usersToInformAboutTrojanHorse->add($alertUser);
            }
            return true;
        }

        //ships of friends dont attack
        if ($this->playerRelationDeterminator->isFriend($alertUser, $incomingShipUser)) {
            return true;
        }

        //ships in finished tholian web dont attack
        $holdingWeb = $alertedShip->getHoldingWeb();
        if ($holdingWeb !== null && $holdingWeb->isFinished()) {
            return true;
        }
        return $this->skipDueToPirateProtection(
            $incomingShipUser,
            $alertedShip
        );
    }

    private function skipDueToPirateProtection(
        UserInterface $incomingShipUser,
        ShipInterface $alertShip
    ): bool {

        //pirates don't attack if user is protected
        $pirateWrath = $incomingShipUser->getPirateWrath();
        if (
            $alertShip->getUserId() === UserEnum::USER_NPC_KAZON
            && $pirateWrath !== null
            && $pirateWrath->getProtectionTimeout() > $this->stuTime->time()
        ) {
            return true;
        }

        //players don't attack pirates if protection is active
        $pirateWrath = $alertShip->getUser()->getPirateWrath();
        return $incomingShipUser->getId() === UserEnum::USER_NPC_KAZON
        && $pirateWrath !== null
        && $pirateWrath->getProtectionTimeout() > $this->stuTime->time();
    }
}
