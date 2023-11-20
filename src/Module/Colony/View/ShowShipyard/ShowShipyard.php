<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipyard;

use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\BuildableRumpListItemInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class ShowShipyard implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIPYARD';

    private ColonyLoaderInterface $colonyLoader;

    private ShowShipyardRequestInterface $showShipyardRequest;

    private BuildingFunctionRepositoryInterface $buildingFunctionRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowShipyardRequestInterface $showShipyardRequest,
        BuildingFunctionRepositoryInterface $buildingFunctionRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showShipyardRequest = $showShipyardRequest;
        $this->buildingFunctionRepository = $buildingFunctionRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showShipyardRequest->getColonyId(),
            $userId,
            false
        );

        $function = $this->buildingFunctionRepository->find(
            $this->showShipyardRequest->getBuildingFunctionId()
        );

        $game->showMacro(ColonyMenuEnum::MENU_SHIPYARD->getTemplate());

        $game->setTemplateVar('CURRENT_MENU', ColonyMenuEnum::MENU_SHIPYARD);
        $game->setTemplateVar('COLONY', $colony);

        $game->setTemplateVar(
            'BUILDABLE_SHIPS',
            array_map(
                fn (ShipRumpInterface $shipRump): BuildableRumpListItemInterface => $this->colonyLibFactory->createBuildableRumpItem(
                    $shipRump,
                    $user
                ),
                $this->shipRumpRepository->getBuildableByUserAndBuildingFunction($userId, $function->getFunction())
            )
        );
        $game->setTemplateVar('BUILDABLE_RUMPS', $this->shipRumpRepository->getBuildableByUser($userId));
    }
}
