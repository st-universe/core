<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipyard;

use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Colony\Lib\BuildableRumpListItemInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\Lib\ColonyMenu;
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
            $userId
        );

        $function = $this->buildingFunctionRepository->find(
            $this->showShipyardRequest->getBuildingFunctionId()
        );

        $game->showMacro('html/colonymacros.xhtml/cm_shipyard');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(ColonyEnum::MENU_SHIPYARD));

        $game->setTemplateVar(
            'BUILDABLE_SHIPS',
            array_map(
                function (ShipRumpInterface $shipRump) use ($user): BuildableRumpListItemInterface {
                    return $this->colonyLibFactory->createBuildableRumpItem(
                        $shipRump,
                        $user
                    );
                },
                $this->shipRumpRepository->getBuildableByUserAndBuildingFunction($userId, $function->getFunction())
            )
        );
        $game->setTemplateVar('BUILDABLE_RUMPS', $this->shipRumpRepository->getBuildableByUser($userId));
    }
}
