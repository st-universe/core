<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use RuntimeException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\AttackingBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;

final class SpacecraftAttackCore implements SpacecraftAttackCoreInterface
{
    public function __construct(
        private DistributedMessageSenderInterface $distributedMessageSender,
        private SpacecraftAttackCycleInterface $spacecraftAttackCycle,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private FightLibInterface $fightLib,
        private TholianWebUtilInterface $tholianWebUtil,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private BattlePartyFactoryInterface $battlePartyFactory
    ) {}

    #[\Override]
    public function attack(
        SpacecraftWrapperInterface|FleetWrapperInterface $sourceWrapper,
        SpacecraftWrapperInterface $targetWrapper,
        bool $isAttackingShieldsOnly,
        bool &$isFleetFight,
        InformationWrapper $informations
    ): bool {
        $wrapper = $sourceWrapper instanceof SpacecraftWrapperInterface ? $sourceWrapper : $sourceWrapper->getLeadWrapper();
        $ship = $wrapper->get();

        $target = $targetWrapper->get();
        $userId = $ship->getUser()->getId();
        $isTargetBase = $target->isStation();

        $isActiveTractorShipWarped = $this->isActiveTractorShipWarped($ship, $target);

        [$attacker, $defender, $isFleetFight, $attackCause] = $this->getAttackersAndDefenders(
            $wrapper,
            $targetWrapper,
            $isAttackingShieldsOnly
        );

        $messageCollection = $this->spacecraftAttackCycle->cycle($attacker, $defender, $attackCause);

        $this->sendPms(
            $userId,
            $ship->getSectorString(),
            $messageCollection,
            ($attackCause !== SpacecraftAttackCauseEnum::THOLIAN_WEB_REFLECTION) && $isTargetBase
        );

        $informations->addInformationWrapper($messageCollection->getInformationDump());

        if ($isActiveTractorShipWarped) {
            //Alarm-Rot check for ship
            if (!$ship->getCondition()->isDestroyed()) {
                $this->alertReactionFacade->doItAll($wrapper, $informations);
            }

            //Alarm-Rot check for traktor ship
            if (!$target->getCondition()->isDestroyed()) {
                $this->alertReactionFacade->doItAll($targetWrapper, $informations);
            }
        }

        return !$ship->getCondition()->isDestroyed();
    }

    private function isActiveTractorShipWarped(Spacecraft $spacecraft, Spacecraft $target): bool
    {
        $tractoringShip = $spacecraft instanceof Ship ? $spacecraft->getTractoringSpacecraft() : null;
        if ($tractoringShip === null) {
            return false;
        }

        if ($tractoringShip !== $target) {
            return false;
        } else {
            return $target->getWarpDriveState();
        }
    }

    private function sendPms(
        int $userId,
        string $sectorString,
        MessageCollectionInterface $messageCollection,
        bool $isTargetBase
    ): void {

        $header = sprintf(
            _("Kampf in Sektor %s"),
            $sectorString
        );

        $this->distributedMessageSender->distributeMessageCollection(
            $messageCollection,
            $userId,
            $isTargetBase ? PrivateMessageFolderTypeEnum::SPECIAL_STATION : PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $header
        );
    }

    /**
     * @return array{0: AttackingBattleParty, 1: BattlePartyInterface, 2: bool, 3: SpacecraftAttackCauseEnum}
     */
    private function getAttackersAndDefenders(
        SpacecraftWrapperInterface|FleetWrapperInterface $wrapper,
        SpacecraftWrapperInterface $targetWrapper,
        bool $isAttackingShieldsOnly
    ): array {
        $attackCause = SpacecraftAttackCauseEnum::SHIP_FIGHT;
        $ship = $wrapper instanceof SpacecraftWrapperInterface ? $wrapper->get() : $wrapper->get()->getLeadShip();

        [$attacker, $defender, $isFleetFight] = $this->fightLib->getAttackersAndDefenders(
            $wrapper,
            $targetWrapper,
            $isAttackingShieldsOnly,
            $this->battlePartyFactory
        );

        $isTargetOutsideFinishedWeb = $this->tholianWebUtil->isTargetOutsideFinishedTholianWeb($ship, $targetWrapper->get());

        //if in tholian web and defenders outside, reflect damage
        if ($isTargetOutsideFinishedWeb) {
            $attackCause = SpacecraftAttackCauseEnum::THOLIAN_WEB_REFLECTION;
            $holdingWeb = $ship->getHoldingWeb();
            if ($holdingWeb === null) {
                throw new RuntimeException('this should not happen');
            }

            $defender = $this->battlePartyFactory->createMixedBattleParty(
                $this->spacecraftWrapperFactory->wrapSpacecrafts($holdingWeb->getCapturedSpacecrafts()->toArray())
            );
        } elseif ($this->tholianWebUtil->isTargetInsideFinishedTholianWeb(
            $ship,
            $targetWrapper->get()
        )) {
            $attackCause = SpacecraftAttackCauseEnum::THOLIAN_WEB_REFLECTION;
        }

        return [
            $attacker,
            $defender,
            $isFleetFight,
            $attackCause
        ];
    }
}
