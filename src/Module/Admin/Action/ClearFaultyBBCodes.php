<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use JBBCode\Parser;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ClearFaultyBBCodes implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CORRUPT_BBCODES';

    private UserRepositoryInterface $userRepository;

    private ShipRepositoryInterface $shipRepository;

    private FleetRepositoryInterface $fleetRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private AllianceRepositoryInterface $allianceRepository;

    private Parser $bbCodeParser;

    public function __construct(
        UserRepositoryInterface $userRepository,
        ShipRepositoryInterface $shipRepository,
        FleetRepositoryInterface $fleetRepository,
        ColonyRepositoryInterface $colonyRepository,
        AllianceRepositoryInterface $allianceRepository,
        Parser $bbCodeParser
    ) {
        $this->userRepository = $userRepository;
        $this->shipRepository = $shipRepository;
        $this->fleetRepository = $fleetRepository;
        $this->colonyRepository = $colonyRepository;
        $this->allianceRepository = $allianceRepository;
        $this->bbCodeParser = $bbCodeParser;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowScripts::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        //USERS
        $game->addInformation("USERS:");
        $allUsers = $this->userRepository->findAll();
        foreach ($allUsers as $user) {
            if (!CleanTextUtils::checkBBCode($user->getName())) {
                $game->addInformationf(_("user_id: %d, name: %s"), $user->getId(), $user->getName());

                $textOnly = $this->bbCodeParser->parse($user->getName())->getAsText();

                $user->setUsername($textOnly);
                $this->userRepository->save($user);
            }
        }
        $game->addInformation("Usernamen wurde bereinigt!");

        //SHIPS
        $game->addInformation("SHIPS:");
        $allShips = $this->shipRepository->findAll();
        foreach ($allShips as $ship) {
            if (!CleanTextUtils::checkBBCode($ship->getName())) {
                $game->addInformationf(_("ship_id: %d, name: %s"), $ship->getId(), $ship->getName());

                $textOnly = $this->bbCodeParser->parse($ship->getName())->getAsText();

                $ship->setName($textOnly);
                $this->shipRepository->save($ship);
            }
        }
        $game->addInformation("Schiffsnamen wurde bereinigt!");

        //FLEETS
        $game->addInformation("FLEETS:");
        $allFleets = $this->fleetRepository->findAll();
        foreach ($allFleets as $fleet) {
            if (!CleanTextUtils::checkBBCode($fleet->getName())) {
                $game->addInformationf(_("fleet_id: %d, name: %s"), $fleet->getId(), $fleet->getName());

                $textOnly = $this->bbCodeParser->parse($fleet->getName())->getAsText();

                $fleet->setName($textOnly);
                $this->fleetRepository->save($fleet);
            }
        }
        $game->addInformation("Flottennamen wurde bereinigt!");

        //COLONIES
        $game->addInformation("COLONIES:");
        $allColonies = $this->colonyRepository->findAll();
        foreach ($allColonies as $colony) {
            if (!CleanTextUtils::checkBBCode($colony->getName())) {
                $game->addInformationf(_("colony_id: %d, name: %s"), $colony->getId(), $colony->getName());

                $textOnly = $this->bbCodeParser->parse($colony->getName())->getAsText();

                $colony->setName($textOnly);
                $this->colonyRepository->save($colony);
            }
        }
        $game->addInformation("Kolonienamen wurde bereinigt!");

        //ALLIANCES
        $game->addInformation("ALLIANCES:");
        $allAllys = $this->allianceRepository->findAll();
        foreach ($allAllys as $ally) {
            if (!CleanTextUtils::checkBBCode($ally->getName())) {
                $game->addInformationf(_("alliance_id: %d, name: %s"), $ally->getId(), $ally->getName());

                $textOnly = $this->bbCodeParser->parse($ally->getName())->getAsText();

                $ally->setName($textOnly);
                $this->allianceRepository->save($ally);
            }
        }
        $game->addInformation("Allianznamen wurde bereinigt!");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
