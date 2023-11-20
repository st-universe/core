<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowBuildPlans;

use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\BuildPlanDeleterInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class ShowBuildPlans implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILDPLANS';

    private ColonyLoaderInterface $colonyLoader;

    private ShowBuildPlansRequestInterface $showBuildPlansRequest;

    private BuildingFunctionRepositoryInterface $buildingFunctionRepository;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private BuildPlanDeleterInterface $buildPlanDeleter;

    public function __construct(
        BuildPlanDeleterInterface $buildPlanDeleter,
        ColonyLoaderInterface $colonyLoader,
        ShowBuildPlansRequestInterface $showBuildPlansRequest,
        BuildingFunctionRepositoryInterface $buildingFunctionRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showBuildPlansRequest = $showBuildPlansRequest;
        $this->buildingFunctionRepository = $buildingFunctionRepository;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->buildPlanDeleter = $buildPlanDeleter;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showBuildPlansRequest->getColonyId(),
            $userId,
            false
        );

        $buildingFunction = $this->buildingFunctionRepository->find(
            $this->showBuildPlansRequest->getBuildingFunctionId()
        );
        if ($buildingFunction === null) {
            return;
        }

        $game->showMacro(ColonyMenuEnum::MENU_BUILDPLANS->getTemplate());
        $game->setTemplateVar('CURRENT_MENU', ColonyMenuEnum::MENU_BUILDPLANS);

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar(
            'AVAILABLE_BUILDPLANS',
            array_map(
                fn (ShipBuildplanInterface $plan): array => [
                    'plan' => $plan,
                    'deletable' => $this->buildPlanDeleter->isDeletable($plan)
                ],
                $this->shipBuildplanRepository->getByUserAndBuildingFunction(
                    $userId,
                    $buildingFunction->getFunction()
                )
            )
        );
    }
}
