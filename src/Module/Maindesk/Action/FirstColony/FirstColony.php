<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\FirstColony;

use Stu\Exception\AccessViolation;
use Stu\Module\Colony\Lib\PlanetColonizationInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class FirstColony implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FIRST_COLONY';

    private FirstColonyRequestInterface $firstColonyRequest;

    private BuildingRepositoryInterface $buildingRepository;

    private PlanetColonizationInterface $planetColonization;

    private ColonyRepositoryInterface $colonyRepository;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        FirstColonyRequestInterface $firstColonyRequest,
        BuildingRepositoryInterface $buildingRepository,
        PlanetColonizationInterface $planetColonization,
        ColonyRepositoryInterface $colonyRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->firstColonyRequest = $firstColonyRequest;
        $this->buildingRepository = $buildingRepository;
        $this->planetColonization = $planetColonization;
        $this->colonyRepository = $colonyRepository;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        if ((int) $user->getState() !== 1) {
            throw new AccessViolation();
        }

        $planetId = $this->firstColonyRequest->getPlanetId();

        $colony = $this->colonyRepository->find($planetId);

        if ($colony === null || !$colony->isFree()) {
            $game->addInformation(_('"Dieser Planet wurde bereits besiedelt'));
            return;
        }
        $colonyList = $this->colonyRepository->getStartingByFaction((int) $user->getFactionId());

        if (!array_key_exists($planetId, $colonyList)) {
            return;
        }

        $this->planetColonization->colonize(
            $colony,
            $user->getId(),
            $this->buildingRepository->find($user->getFaction()->getStartBuildingId())
        );

        $user->setState(UserEnum::USER_STATE_TUTORIAL1);

        $this->userRepository->save($user);

        // Database entries for colonyclass
        $game->checkDatabaseItem($colony->getColonyClass()->getDatabaseId());

        $game->redirectTo('./colony.php?id=' . $colony->getId());
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
