<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DeactivateBuilding;

use request;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class DeactivateBuilding implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEACTIVATE';

    private ColonyLoaderInterface $colonyLoader;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    private BuildingActionInterface $buildingAction;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        PlanetFieldRepositoryInterface $planetFieldRepository,
        BuildingActionInterface $buildingAction
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->planetFieldRepository = $planetFieldRepository;
        $this->buildingAction = $buildingAction;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $field = $this->planetFieldRepository->getByColonyAndFieldId(
            $colony->getId(),
            (int) request::indInt('fid')
        );

        if ($field === null) {
            return;
        }

        if ($field->isUnderConstruction()) {
            $field->setActivateAfterBuild(false);
            $game->addInformation("Gebäude wird nach Bau deaktiviert");
        } else {
            $this->buildingAction->deactivate(
                $colony,
                $field,
                $game
            );
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
