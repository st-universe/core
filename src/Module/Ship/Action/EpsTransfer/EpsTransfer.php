<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\EpsTransfer;

use request;
use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class EpsTransfer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ETRANSFER';

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
        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrewCount() == 0) {
            $game->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $ship->getBuildplan()->getCrew()
            );
            return;
        }

        if ($ship->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->getWarpState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        $target = $this->shipRepository->find(request::postIntFatal('target'));
        if ($target === null) {
            return;
        }
        if (!$ship->canInteractWith($target)) {
            return;
        }
        if ($target->getWarpState()) {
            $game->addInformation(sprintf(_('Die %s befindet sich im Warp'), $target->getName()));
            return;
        }
        $load = request::postInt('ecount');
        if ($load < 1) {
            $game->addInformation(_("Es wurde keine Energiemenge angegeben"));
            return;
        }
        if ($target->getEBatt() >= $target->getMaxEBatt()) {
            $game->addInformation(sprintf(_('Der Energiespeicher der %s ist voll'), $target->getName()));
            return;
        }
        if ($load * 3 > $ship->getEps()) {
            $load = floor($ship->getEps() / 3);
        }
        if ($load + $target->getEbatt() > $target->getMaxEbatt()) {
            $load = $target->getMaxEbatt() - $target->getEbatt();
        }
        $ship->setEps($ship->getEps() - $load * 3);
        $target->setEBatt($target->getEBatt() + $load);

        $this->shipRepository->save($target);
        $this->shipRepository->save($ship);

        $this->privateMessageSender->send(
            $userId,
            (int)$target->getUserId(),
            "Die " . $ship->getName() . " transferiert in SeKtor " . $ship->getSectorString() . " " . $load . " Energie in die Batterie der " . $target->getName(),
            PM_SPECIAL_TRADE
        );
        $game->addInformation(sprintf(_('Es wurde %d Energie zur %s transferiert'), $load, $target->getName()));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
