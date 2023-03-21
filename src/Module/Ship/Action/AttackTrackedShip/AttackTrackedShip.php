<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AttackTrackedShip;

use request;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Battle\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCycleInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;

final class AttackTrackedShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ATTACK_TRACKED';

    private ShipLoaderInterface $shipLoader;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipAttackCycleInterface $shipAttackCycle;

    private InteractionCheckerInterface $interactionChecker;

    private AlertRedHelperInterface $alertRedHelper;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipAttackCycleInterface $shipAttackCycle,
        InteractionCheckerInterface $interactionChecker,
        AlertRedHelperInterface $alertRedHelper,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipAttackCycle = $shipAttackCycle;
        $this->interactionChecker = $interactionChecker;
        $this->alertRedHelper = $alertRedHelper;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $shipId = request::getIntFatal('id');
        $targetId = request::getIntFatal('target');

        $shipArray = $this->shipLoader->getWrappersByIdAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $shipArray[$shipId];
        $ship = $wrapper->get();

        $targetWrapper = $shipArray[$targetId];
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        $tracker = $wrapper->getTrackerSystemData();
        if ($tracker === null || $tracker->targetId !== $target->getId()) {
            return;
        }

        if (!$ship->canAttack()) {
            $game->addInformation(_('Waffen sind offline'));
            return;
        }

        if ($target->getUser()->isVacationRequestOldEnough()) {
            $game->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }
        if (!$this->interactionChecker->checkPosition($target, $ship)) {
            throw new SanityCheckException('InteractionChecker->checkPosition failed', self::ACTION_IDENTIFIER);
        }

        if ($wrapper->getEpsSystemData()->getEps() == 0) {
            $game->addInformation(_('Keine Energie vorhanden'));
            return;
        }
        if ($ship->isDisabled()) {
            $game->addInformation(_('Das Schiff ist kampfunfähig'));
            return;
        }
        if ($ship->getDockedTo()) {
            $ship->setDockedTo(null);
        }

        $isTargetBase = $target->isBase();
        $isShipWarped = $ship->getWarpState();
        $isTargetWarped = $target->getWarpState();

        [$attacker, $defender, $fleet] = $this->getAttackerDefender($ship, $target);

        $fightMessageCollection = $this->shipAttackCycle->cycle(
            $this->shipWrapperFactory->wrapShips($attacker),
            $this->shipWrapperFactory->wrapShips($defender)
        );

        $messageDump = $fightMessageCollection->getMessageDump();

        $pm = sprintf(_('Kampf in Sektor %s') . "\n", $ship->getSectorString());
        foreach ($messageDump as $value) {
            $pm .= $value . "\n";
        }
        $this->privateMessageSender->send(
            $userId,
            $target->getUser()->getId(),
            $pm,
            $isTargetBase ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );

        $msg = $messageDump;

        //Alarm-Rot check for ship
        if ($isShipWarped && !$ship->isDestroyed()) {
            $msg = array_merge($msg, $this->alertRedHelper->doItAll($ship, null));
        }

        //Alarm-Rot check for traktor ship
        if ($isTargetWarped && !$target->isDestroyed()) {
            $msg = array_merge($msg, $this->alertRedHelper->doItAll($target, null));
        }

        if ($ship->isDestroyed()) {
            $game->addInformationMerge($msg);
            return;
        }
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        if ($fleet) {
            $game->addInformation(_("Angriff durchgeführt"));
            $game->setTemplateVar('FIGHT_RESULTS', $msg);
        } else {
            $game->addInformationMerge($msg);
            $game->setTemplateVar('FIGHT_RESULTS', null);
        }
    }

    private function getAttackerDefender(ShipInterface $ship, ShipInterface $target): array
    {
        $fleet = false;

        if ($ship->isFleetLeader()) {
            $attacker = $ship->getFleet()->getShips()->toArray();
            $fleet = true;
        } else {
            $attacker = [$ship->getId() => $ship];
        }
        if ($target->getFleet() !== null) {
            $defender = [];

            // only uncloaked defenders fight
            /**
             * @var ShipInterface $defShip
             */
            foreach ($target->getFleet()->getShips()->toArray() as $defShip) {
                if (!$defShip->getCloakState()) {
                    $defender[$defShip->getId()] = $defShip;

                    if (
                        $defShip->getDockedTo() !== null
                        && !$defShip->getDockedTo()->getUser()->isNpc()
                        && $defShip->getDockedTo()->canAttack()
                    ) {
                        $defender[$defShip->getDockedTo()->getId()] = $defShip->getDockedTo();
                    }
                }
            }

            // if all defenders were cloaked, they obviously were scanned and enter the fight as a whole fleet
            if (empty($defender)) {
                $defender = $target->getFleet()->getShips()->toArray();
            }

            $fleet = true;
        } else {
            $defender = [$target->getId() => $target];

            if (
                $target->getDockedTo() !== null
                && !$target->getDockedTo()->getUser()->isNpc()
                && $target->getDockedTo()->canAttack()
            ) {
                $defender[$target->getDockedTo()->getId()] = $target->getDockedTo();
            }
        }

        return [
            $attacker,
            $defender,
            $fleet
        ];
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
