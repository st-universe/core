<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SalvageEmergencyPods;

use request;
use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class SalvageEmergencyPods implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SALVAGE_EPODS';

    private $shipLoader;

    private $shipCrewRepository;

    private $privateMessageSender;

    private $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipCrewRepositoryInterface $shipCrewRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
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
        $ship->canInteractWith($target);
        if ($target->getCrewCount() == 0) {
            $game->addInformation(_('Keine Rettungskapseln vorhanden'));
            return;
        }
        if ($ship->getEps() < 1) {
            $game->addInformation(sprintf(_('Zum Bergen der Rettungskapseln wird %d Energie benötigt'), 1));
            return;
        }
        $ship->cancelRepair();
        $dummy_crew = current($target->getCrewList());
        if ($dummy_crew->getCrew()->getUserId() != $userId) {
            $this->privateMessageSender->send(
                $userId,
                $dummy_crew->getCrew()->getUserId(),
                sprintf(
                    _('Der Siedler hat %d deiner Crewmitglieder von einem Trümmerfeld geborgen.'),
                    $target->getCrewCount()
                ),
                PM_SPECIAL_SHIP
            );
        }
        $this->shipCrewRepository->truncateByShip((int) $target->getId());

        $ship->setEps($ship->getEps() - 1);

        $this->shipRepository->save($ship);

        $game->addInformation(_('Die Rettungskapseln wurden geborgen'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
