<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\SwitchColonyMenu;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Building\BuildingFunctionTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\BuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class SwitchColonyMenu implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SWITCH_COLONYMENU';

    private PlanetFieldHostProviderInterface $planetFieldHostProvider;

    private BuildingFunctionRepositoryInterface $buildingFunctionRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    public function __construct(
        PlanetFieldHostProviderInterface $planetFieldHostProvider,
        BuildingFunctionRepositoryInterface $buildingFunctionRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository
    ) {
        $this->planetFieldHostProvider = $planetFieldHostProvider;
        $this->buildingFunctionRepository = $buildingFunctionRepository;
        $this->planetFieldRepository = $planetFieldRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $host = $this->planetFieldHostProvider->loadHostViaRequestParameters($game->getUser());

        $menu = ColonyMenuEnum::getFor(request::getIntFatal('menu'));

        if (!$host->isMenuAllowed($menu)) {
            $game->addInformation('Dieses Menü ist nicht für die Sandbox geeignet');
            $game->setView($host->getDefaultViewIdentifier());
            return;
        }

        $game->setView($menu->getViewIdentifier());

        $neededBuildingFunctions = $menu->getNeededBuildingFunction();
        if (
            $neededBuildingFunctions !== null
            && !$this->hasSpecialBuilding($host, $neededBuildingFunctions)
        ) {
            return;
        }
        if (BuildingFunctionTypeEnum::isBuildingFunctionMandatory($menu)) {
            $func = $this->buildingFunctionRepository->find(request::getIntFatal('func'));
            $game->setTemplateVar('FUNC', $func);
        }
    }

    /** @param array<int> $functions */
    private function hasSpecialBuilding(PlanetFieldHostInterface $host, array $functions): bool
    {
        return $this->planetFieldRepository->getCountByColonyAndBuildingFunctionAndState(
            $host,
            $functions,
            [0, 1]
        ) > 0;
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
