<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\FirstColony;

use AccessViolation;
use Colony;
use Stu\Module\Colony\Lib\PlanetColonizationInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

final class FirstColony implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FIRST_COLONY';

    private $firstColonyRequest;

    private $factionRepository;

    private $buildingRepository;

    private $planetColonization;

    public function __construct(
        FirstColonyRequestInterface $firstColonyRequest,
        FactionRepositoryInterface $factionRepository,
        BuildingRepositoryInterface $buildingRepository,
        PlanetColonizationInterface $planetColonization
    ) {
        $this->firstColonyRequest = $firstColonyRequest;
        $this->factionRepository = $factionRepository;
        $this->buildingRepository = $buildingRepository;
        $this->planetColonization = $planetColonization;
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

        $faction = $this->factionRepository->find((int) $user->getFaction());

        $this->planetColonization->colonize(
            $colony,
            $user->getId(),
            $this->buildingRepository->find($faction->getStartBuildingId())
        );

        $user->setActive(2);
        $user->save();

        // Database entries for planettype
        $game->checkDatabaseItem($colony->getPlanetType()->getDatabaseId());

        $game->redirectTo('./colony.php?id=' . $colony->getId());
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
