<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TroopTransfer;

use request;

use Stu\Component\Crew\CrewEnum;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\TroopTransferUtilityInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class TroopTransfer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TROOP_TRANSFER';

    private ShipLoaderInterface $shipLoader;

    private ShipStorageManagerInterface $shipStorageManager;

    private ShipRepositoryInterface $shipRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private TroopTransferUtilityInterface $transferUtility;
    
    private ShipCrewRepositoryInterface $shipCrewRepository;

    private CrewRepositoryInterface $crewRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipStorageManagerInterface $shipStorageManager,
        ShipRepositoryInterface $shipRepository,
        ColonyRepositoryInterface $colonyRepository,
        TroopTransferUtilityInterface $transferUtility,
        ShipCrewRepositoryInterface $shipCrewRepository,
        CrewRepositoryInterface $crewRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipStorageManager = $shipStorageManager;
        $this->shipRepository = $shipRepository;
        $this->colonyRepository = $colonyRepository;
        $this->transferUtility = $transferUtility;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->crewRepository = $crewRepository;
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

        echo "- 1: \n";
        
        if ($ship->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        echo "- 2: \n";
        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        echo "- 3: \n";
        if ($ship->getWarpState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        echo "- 4: \n";
        if ($ship->getShieldState()) {
            $game->addInformation(_("Die Schilde sind aktiviert"));
            return;
        }
        echo "- 5: \n";
        
        $isColony = request::has('isColony');
        $isUnload = request::has('isUnload');
        
        if ($isColony)
        {
            echo "- 6: \n";
            $target = $this->colonyRepository->find((int)request::postIntFatal('target'));
        } else {
            $target = $this->shipRepository->find((int)request::postIntFatal('target'));
        }
        
        
        if ($target === null) {
            return;
        }
        echo "- 7: \n";
        if (!$ship->canInteractWith($target, $isColony, !$isColony)) {
            return;
        }
        echo "- 8: \n";
        if (!$isColony && $target->getWarpState()) {
            $game->addInformation(sprintf(_('Die %s befindet sich im Warp'), $target->getName()));
            return;
        }
        echo "- 9: \n";
        $requestedTransferCount = request::postInt('tcount');
        
        $transferAmount = $ship->getBeamFactor();
        
        if (ceil($requestedTransferCount / $transferAmount) > $ship->getEps()) {
            $requestedTransferCount = $ship->getEps() * $transferAmount;
        }
        
        if ($isColony)
        {
            
            echo "- 10: \n";
            if ($isUnload)
            {
                $amount = min($requestedTransferCount, $this->transferUtility->getBeamableTroopCount($ship));
                
                $array = $ship->getCrewlist()->getValues();
                
                for ($i = 0; $i < $amount; $i++) {
                    $this->shipCrewRepository->delete($array[$i]); 
                }
            }
            else {
                echo "- 11: \n";
                $amount = min($requestedTransferCount, min($ship->getUser()->getFreeCrewCount(),
                $this->transferUtility->getFreeQuarters($ship)));
                
                for ($i = 0; $i < $amount; $i++) {
                    echo "- 12: \n";
                    $crew = $this->crewRepository->getFreeByUser($userId);

                    $sc = $this->shipCrewRepository->prototype();
                    $sc->setCrew($crew);
                    $sc->setShip($ship);
                    $sc->setUser($ship->getUser());
                    $sc->setSlot(CrewEnum::CREW_TYPE_CREWMAN);
    
                    $this->shipCrewRepository->save($sc);
                }
            }
            
        }
        else
        {
            if ($isUnload)
            {
                $amount = min($requestedTransferCount, min($this->transferUtility->getBeamableTroopCount($ship),
                            $this->transferUtility->getFreeQuarters($target)));

                $array = $ship->getCrewlist()->getValues();

                for ($i = 0; $i < $amount; $i++) {
                    $sc = $array[$i];
                    $sc->setShip($target);
                    $this->shipCrewRepository->save($sc);
                }
            }
            else {
                $amount = min($requestedTransferCount, min($target->getCrewCount(),
                            $this->transferUtility->getFreeQuarters($ship)));

                $array = $target->getCrewlist()->getValues();

                for ($i = 0; $i < $amount; $i++) {
                    $sc = $array[$i];
                    $sc->setShip($ship);
                    $this->shipCrewRepository->save($sc);
                }
            }

        }

        $game->addInformation(
            sprintf(
                _('Die %s hat %d Crewman %s der %s transferiert'),
                $ship->getName(),
                $amount,
                $isUnload ? 'zu' : 'von',
                $target->getName()
            )
        );

        $this->shipRepository->save($ship);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
