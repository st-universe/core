<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Module\Control\StuTime;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;

//TODO convert conditions to implementations of SkipConditionInterface
class SkipDetection implements SkipDetectionInterface
{
    public function __construct(
        private PlayerRelationDeterminatorInterface $playerRelationDeterminator,
        private StuTime $stuTime
    ) {}

    #[Override]
    public function isSkipped(
        Spacecraft $incomingSpacecraft,
        SpacecraftWrapperInterface $alertedWrapper,
        ?Spacecraft $tractoringSpacecraft,
        Collection $usersToInformAboutTrojanHorse
    ): bool {

        $alertedSpacecraft = $alertedWrapper->get();
        $alertUser = $alertedSpacecraft->getUser();
        $incomingShipUser = $incomingSpacecraft->getUser();

        //alert yellow only attacks if incoming is foe
        if (
            $alertedWrapper->getAlertState() === SpacecraftAlertStateEnum::ALERT_YELLOW
            && !$this->playerRelationDeterminator->isEnemy($alertUser, $incomingShipUser)
        ) {
            return true;
        }

        //now ALERT-MODE: RED or YELLOW+ENEMY

        //ships of friends from tractoring ship dont attack
        if (
            $tractoringSpacecraft !== null
            && $this->playerRelationDeterminator->isFriend($alertUser, $tractoringSpacecraft->getUser())
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
        $holdingWeb = $alertedSpacecraft->getHoldingWeb();
        if ($holdingWeb !== null && $holdingWeb->isFinished()) {
            return true;
        }

        return $this->skipDueToPirateProtection(
            $incomingShipUser,
            $alertedSpacecraft
        );
    }

    private function skipDueToPirateProtection(
        User $incomingShipUser,
        Spacecraft $alertedSpacecraft
    ): bool {

        $time = $this->stuTime->time();

        //pirates don't attack new players
        if ($incomingShipUser->getRegistration()->getCreationDate() > $time - TimeConstants::EIGHT_WEEKS_IN_SECONDS) {
            return true;
        }

        //pirates don't attack if user is protected
        $pirateWrath = $incomingShipUser->getPirateWrath();
        if (
            $alertedSpacecraft->getUserId() === UserEnum::USER_NPC_KAZON
            && $pirateWrath !== null
            && $pirateWrath->getProtectionTimeout() > $time
        ) {
            return true;
        }

        //players don't attack pirates if protection is active
        $pirateWrath = $alertedSpacecraft->getUser()->getPirateWrath();
        return $incomingShipUser->getId() === UserEnum::USER_NPC_KAZON
            && $pirateWrath !== null
            && $pirateWrath->getProtectionTimeout() > $time;
    }
}
