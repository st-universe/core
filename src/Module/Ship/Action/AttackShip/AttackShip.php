<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AttackShip;

use request;
use Stu\Module\Communication\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipAttackCycleInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class AttackShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ATTACK_SHIP';

    private $shipLoader;

    private $privateMessageSender;

    private $shipRepository;

    private $shipAttackCycle;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository,
        ShipAttackCycleInterface $shipAttackCycle
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
        $this->shipAttackCycle = $shipAttackCycle;
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

        if ($target->getUser()->getId() == $userId) {
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
        $target_user_id = $target->getUserId();
        if ($ship->isFleetLeader()) {
            $attacker = $ship->getFleet()->getShips()->toArray();
            $fleet = true;
        } else {
            $attacker = [$ship->getId() => $ship];
        }
        if ($target->getFleetId()) {
            $defender = $target->getFleet()->getShips()->toArray();
            $fleet = true;
        } else {
            $defender = [$target->getId() => $target];
        }
        $this->shipAttackCycle->init($attacker, $defender);
        $this->shipAttackCycle->cycle();

        $pm = sprintf(_('Kampf in Sektor %d|%d') . "\n", $ship->getPosX(), $ship->getPosY());
        foreach ($this->shipAttackCycle->getMessages() as $key => $value) {
            $pm .= $value . "\n";
        }
        $this->privateMessageSender->send(
            $userId,
            (int)$target_user_id,
            $pm,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
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
