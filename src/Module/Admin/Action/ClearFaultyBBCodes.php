<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use JBBCode\Parser;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ClearFaultyBBCodes implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CORRUPT_BBCODES';

    public function __construct(private UserRepositoryInterface $userRepository, private ShipRepositoryInterface $shipRepository, private FleetRepositoryInterface $fleetRepository, private ColonyRepositoryInterface $colonyRepository, private AllianceRepositoryInterface $allianceRepository, private Parser $bbCodeParser) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowScripts::VIEW_IDENTIFIER);

        // only Admins can trigger ticks

        //USERS
        $context->getInfo()->addInformation("USERS:");
        $allUsers = $this->userRepository->findAll();
        foreach ($allUsers as $user) {
            if (!CleanTextUtils::checkBBCode($user->getName())) {
                $context->getInfo()->addInformationf(_("user_id: %d, name: %s"), $user->getId(), $user->getName());

                $textOnly = $this->bbCodeParser->parse($user->getName())->getAsText();

                $user->setUsername($textOnly);
                $this->userRepository->save($user);
            }
        }
        $context->getInfo()->addInformation("Usernamen wurde bereinigt!");

        //SHIPS
        $context->getInfo()->addInformation("SHIPS:");
        $allShips = $this->shipRepository->findAll();
        foreach ($allShips as $ship) {
            if (!CleanTextUtils::checkBBCode($ship->getName())) {
                $context->getInfo()->addInformationf(_("ship_id: %d, name: %s"), $ship->getId(), $ship->getName());

                $textOnly = $this->bbCodeParser->parse($ship->getName())->getAsText();

                $ship->setName($textOnly);
            }
        }
        $context->getInfo()->addInformation("Schiffsnamen wurde bereinigt!");

        //FLEETS
        $context->getInfo()->addInformation("FLEETS:");
        $allFleets = $this->fleetRepository->findAll();
        foreach ($allFleets as $fleet) {
            if (!CleanTextUtils::checkBBCode($fleet->getName())) {
                $context->getInfo()->addInformationf(_("fleet_id: %d, name: %s"), $fleet->getId(), $fleet->getName());

                $textOnly = $this->bbCodeParser->parse($fleet->getName())->getAsText();

                $fleet->setName($textOnly);
            }
        }
        $context->getInfo()->addInformation("Flottennamen wurde bereinigt!");

        //COLONIES
        $context->getInfo()->addInformation("COLONIES:");
        $allColonies = $this->colonyRepository->findAll();
        foreach ($allColonies as $colony) {
            if (!CleanTextUtils::checkBBCode($colony->getName())) {
                $context->getInfo()->addInformationf(_("colony_id: %d, name: %s"), $colony->getId(), $colony->getName());

                $textOnly = $this->bbCodeParser->parse($colony->getName())->getAsText();

                $colony->setName($textOnly);
                $this->colonyRepository->save($colony);
            }
        }
        $context->getInfo()->addInformation("Kolonienamen wurde bereinigt!");

        //ALLIANCES
        $context->getInfo()->addInformation("ALLIANCES:");
        $allAllys = $this->allianceRepository->findAll();
        foreach ($allAllys as $ally) {
            if (!CleanTextUtils::checkBBCode($ally->getName())) {
                $context->getInfo()->addInformationf(_("alliance_id: %d, name: %s"), $ally->getId(), $ally->getName());

                $textOnly = $this->bbCodeParser->parse($ally->getName())->getAsText();

                $ally->setName($textOnly);
                $this->allianceRepository->save($ally);
            }
        }
        $context->getInfo()->addInformation("Allianznamen wurde bereinigt!");
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
