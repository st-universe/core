<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ActivateShields;

use Override;
use request;

use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class ActivateShields implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ACTIVATE_SHIELDS';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private PlanetFieldRepositoryInterface $planetFieldRepository,
        private BuildingActionInterface $buildingAction
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $fields = $this->planetFieldRepository->getByColonyAndBuildingFunction(
            $colony->getId(),
            [BuildingFunctionEnum::SHIELD_GENERATOR->value]
        );

        if (count($fields) !== 1) {
            return;
        }

        $this->buildingAction->activate(
            current($fields),
            $game
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
