<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AttackShip;

use request;
use Stu\Component\Ship\Nbs\NbsUtilityInterface;
use Stu\Exception\SanityCheckException;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\ShipAttackCycleInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;

final class AttackShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ATTACK_SHIP';

    private ShipLoaderInterface $shipLoader;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipAttackCycleInterface $shipAttackCycle;

    private PositionCheckerInterface $positionChecker;

    private AlertRedHelperInterface $alertRedHelper;

    private NbsUtilityInterface $nbsUtility;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipAttackCycleInterface $shipAttackCycle,
        PositionCheckerInterface $positionChecker,
        AlertRedHelperInterface $alertRedHelper,
        NbsUtilityInterface $nbsUtility
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipAttackCycle = $shipAttackCycle;
        $this->positionChecker = $positionChecker;
        $this->alertRedHelper = $alertRedHelper;
        $this->nbsUtility = $nbsUtility;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::postIntFatal('target');

        $shipArray = $this->shipLoader->getByIdAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $ship = $shipArray[$shipId];
        $target = $shipArray[$targetId];

        if ($target === null) {
            return;
        }

        if ($target->getUser()->isVacationRequestOldEnough()) {
            $game->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }
        if (!$this->positionChecker->checkPosition($target, $ship)) {
            throw new SanityCheckException('PositionChecker->checkPosition failed');
        }

        $isAttackingActiveTractorShip = false;
        $isActiveTractorShipWarped = false;
        if ($ship->isTractored()) {
            if ($ship->getTractoringShip() !== $target) {
                return;
            } else {
                $isAttackingActiveTractorShip = true;
                $isActiveTractorShipWarped = $target->getWarpState();
            }
        }

        if ($target->getIsDestroyed()) {
            $game->setView(ShowShip::VIEW_IDENTIFIER);
            $game->addInformation(_('Das Ziel ist bereits zerstört'));
            return;
        }

        if (!$target->canBeAttacked(!$isAttackingActiveTractorShip)) {
            throw new SanityCheckException('Target cant be attacked');
        }

        if ($target->getCloakState() && !$this->nbsUtility->isTachyonActive($ship)) {
            throw new SanityCheckException('Attacked cloaked ship without active tachyon');
        }

        if ($target->getRump()->isTrumfield()) {
            return;
        }
        if ($ship->getEps() == 0) {
            $game->addInformation(_('Keine Energie vorhanden'));
            return;
        }
        if ($ship->getDisabled()) {
            $game->addInformation(_('Das Schiff ist kampfunfähig'));
            return;
        }
        if ($ship->getDockedTo()) {
            $ship->setDockedTo(null);
        }

        $target_user_id = $target->getUser()->getId();
        $isTargetBase = $target->isBase();

        [$attacker, $defender, $fleet] = $this->getAttackerDefender($ship, $target);

        $this->shipAttackCycle->init($attacker, $defender);
        $this->shipAttackCycle->cycle();

        $pm = sprintf(_('Kampf in Sektor %s') . "\n", $ship->getSectorString());
        foreach ($this->shipAttackCycle->getMessages() as $value) {
            $pm .= $value . "\n";
        }
        $this->privateMessageSender->send(
            $userId,
            (int) $target_user_id,
            $pm,
            $isTargetBase ?  PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );

        $msg = $this->shipAttackCycle->getMessages();

        if ($isActiveTractorShipWarped) {
            //Alarm-Rot check for ship
            if (!$ship->getIsDestroyed()) {
                $msg = array_merge($msg, $this->alertRedHelper->doItAll($ship, null));
            }

            //Alarm-Rot check for traktor ship
            if (!$target->getIsDestroyed()) {
                $msg = array_merge($msg, $this->alertRedHelper->doItAll($target, null));
            }
        }

        if ($ship->getIsDestroyed()) {
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
        if ($target->getFleetId()) {
            $defender = [];

            // only uncloaked defenders fight
            foreach ($target->getFleet()->getShips()->toArray() as $defShip) {
                if (!$defShip->getCloakState()) {
                    $defender[$defShip->getId()] = $defShip;

                    if (
                        $defShip->getDockedTo() !== null
                        && $defShip->getDockedTo()->getUser()->getId() > 100
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
                && $target->getDockedTo()->getUser()->getId() > 100
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
