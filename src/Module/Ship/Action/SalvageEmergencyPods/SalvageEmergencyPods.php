<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SalvageEmergencyPods;

use request;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class SalvageEmergencyPods implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SALVAGE_EPODS';

    private ShipLoaderInterface $shipLoader;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipRepositoryInterface $shipRepository;
    
    private ShipRemoverInterface $shipRemover;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipCrewRepositoryInterface $shipCrewRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository,
        ShipRemoverInterface $shipRemover
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
        $this->shipRemover = $shipRemover;
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

        /** @var ShipCrewInterface $dummy_crew */
        $dummy_crew = $target->getCrewList()->current();
        if ($dummy_crew->getCrew()->getUser()->getId() !== $userId) {
            $this->privateMessageSender->send(
                $userId,
                $dummy_crew->getCrew()->getUser()->getId(),
                sprintf(
                    _('Der Siedler hat %d deiner Crewmitglieder aus Rettungskapseln geborgen.'),
                    $target->getCrewCount()
                ),
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            );
        }
        
        //remove entity if crew was on escape pods
        if ($target->getRump()->isEscapePods())
        {
            $dummy_crew = null;
            echo "- removeEscapePodEntity\n";
            $this->shipRemover->remove($target);
        } else
        {
            $this->shipCrewRepository->truncateByShip((int) $target->getId());
        }

        $ship->setEps($ship->getEps() - 1);

        $this->shipRepository->save($ship);

        $game->addInformation(_('Die Rettungskapseln wurden geborgen'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
