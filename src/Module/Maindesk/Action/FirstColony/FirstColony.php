<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\FirstColony;

use AccessViolation;
use Building;
use Colony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class FirstColony implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_FIRST_COLONY';

    private $firstColonyRequest;

    public function __construct(
        FirstColonyRequestInterface $firstColonyRequest
    ) {
        $this->firstColonyRequest = $firstColonyRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        if ((int) $user->getActive() !== 1) {
            throw new AccessViolation();
        }

        $planetId = $this->firstColonyRequest->getPlanetId();

        $colony = new Colony($planetId);

        if (!$colony->isFree()) {
            $game->addInformation(_('"Dieser Planet wurde bereits besiedelt'));
            return;
        }
        if (!array_key_exists($planetId, Colony::getFreeColonyList($user->getFaction()))) {
            return;
        }

        $faction = ResourceCache()->getObject(CACHE_FACTION, $user->getFaction());
        $colony->colonize($user->getId(), new Building($faction->getBuildingId()));

        $user->setActive(2);
        $user->save();

        // Database entries for planettype
        $game->checkDatabaseItem($colony->getPlanetType()->getDatabaseId());

        DB()->commitTransaction();

        $game->redirectTo('./colony.php?id=' . $colony->getId());
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
