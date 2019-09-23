<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AttackShip;

use request;
use ShipAttackCycle;
use Stu\Module\Communication\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class AttackShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ATTACK_SHIP';

    private $shipLoader;

    private $privateMessageSender;

    private $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $target = $this->shipRepository->find(request::postIntFatal('target'));
        if ($target === null) {
            return;
        }
        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrewCount() == 0) {
            $game->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $ship->getBuildplan()->getCrew()
            );
            return;
        }
        if (!checkPosition($target, $ship)) {
            return;
        }

        if ($target->getUserId() == $userId) {
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
        if ($ship->getDock()) {
            $ship->setDock(0);
        }
        $fleet = false;
        $target_user_id = $target->getUserId();
        if ($ship->isFleetLeader()) {
            $attacker = $ship->getFleet()->getShips();
            $fleet = true;
        } else {
            $attacker = &$ship;
        }
        if ($target->getFleetId()) {
            $defender = $target->getFleet()->getShips();
            $fleet = true;
        } else {
            $defender = &$target;
        }
        $obj = new ShipAttackCycle($attacker, $defender, $ship->getFleetId(), $target->getFleetId());
        $pm = sprintf(_('Kampf in Sektor %d|%d') . "\n", $ship->getPosX(), $ship->getPosY());
        foreach ($obj->getMessages() as $key => $value) {
            $pm .= $value . "\n";
        }
        $this->privateMessageSender->send($userId, (int)$target_user_id, $pm,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP);
        if ($fleet) {
            $game->addInformation(_("Angriff durchgeführt"));
            $game->setTemplateVar('FIGHT_RESULTS', $obj->getMessages());
        } else {
            $game->addInformationMerge($obj->getMessages());
            $game->setTemplateVar('FIGHT_RESULTS', $obj->getMessages());
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
