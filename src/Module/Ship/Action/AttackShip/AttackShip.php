<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AttackShip;

use request;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipAttackCycleInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class AttackShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ATTACK_SHIP';

    private ShipLoaderInterface $shipLoader;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipRepositoryInterface $shipRepository;

    private ShipAttackCycleInterface $shipAttackCycle;

    private PositionCheckerInterface $positionChecker;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository,
        ShipAttackCycleInterface $shipAttackCycle,
        PositionCheckerInterface $positionChecker
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
        $this->shipAttackCycle = $shipAttackCycle;
        $this->positionChecker = $positionChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $target = $this->shipRepository->find(request::postIntFatal('target'));
        if ($target === null) {
            return;
        }

        if ($target->getUser()->isVacationRequestOldEnough()) {
            $game->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));
            return;
        }

        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrewCount() == 0) {
            $game->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $ship->getBuildplan()->getCrew()
            );
            return;
        }
        if (!$this->positionChecker->checkPosition($target, $ship)) {
            return;
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
        $fleet = false;
        $target_user_id = $target->getUser()->getId();
        $isTargetBase = $target->isBase();
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
                    $defender[] = $defShip;
                }
            }

            // if all defenders were cloaked, they obviously were scanned and enter the fight as a whole fleet
            if (empty($defender)) {
                $defender = $target->getFleet()->getShips()->toArray();
            }

            $fleet = true;
        } else {
            $defender = [$target->getId() => $target];
        }
        $this->shipAttackCycle->init($attacker, $defender);
        $this->shipAttackCycle->cycle();

        $pm = sprintf(_('Kampf in Sektor %d|%d') . "\n", $ship->getPosX(), $ship->getPosY());
        foreach ($this->shipAttackCycle->getMessages() as $value) {
            $pm .= $value . "\n";
        }
        $this->privateMessageSender->send(
            $userId,
            (int) $target_user_id,
            $pm,
            $isTargetBase ?  PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
        );

        if ($ship->getIsDestroyed()) {

            $game->addInformationMerge($this->shipAttackCycle->getMessages());
            return;
        }
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        if ($fleet) {
            $game->addInformation(_("Angriff durchgeführt"));
            $game->setTemplateVar('FIGHT_RESULTS', $this->shipAttackCycle->getMessages());
        } else {
            $game->addInformationMerge($this->shipAttackCycle->getMessages());
            $game->setTemplateVar('FIGHT_RESULTS', null);
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
