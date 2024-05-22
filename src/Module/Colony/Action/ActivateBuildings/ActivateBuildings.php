<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ActivateBuildings;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\BuildingActionInterface;
use Stu\Module\Colony\Lib\BuildingMassActionConfigurationInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Orm\Entity\PlanetFieldInterface;

final class ActivateBuildings implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MULTIPLE_ACTIVATION';

    private PlanetFieldHostProviderInterface $planetFieldHostProvider;

    private BuildingActionInterface $buildingAction;

    private BuildingMassActionConfigurationInterface $buildingMassActionConfiguration;

    public function __construct(
        PlanetFieldHostProviderInterface $planetFieldHostProvider,
        BuildingActionInterface $buildingAction,
        BuildingMassActionConfigurationInterface $buildingMassActionConfiguration
    ) {
        $this->planetFieldHostProvider = $planetFieldHostProvider;
        $this->buildingAction = $buildingAction;
        $this->buildingMassActionConfiguration = $buildingMassActionConfiguration;
    }

    public function handle(GameControllerInterface $game): void
    {
        $host = $this->planetFieldHostProvider->loadHostViaRequestParameters($game->getUser());

        $mode = request::indInt('mode');
        $selection = request::getvars()['selection'] ?? request::postvars()['selection'] ?? null;

        $config = $this->buildingMassActionConfiguration->getConfigurations()[$mode] ?? null;

        if ($config === null) {
            return;
        }

        /** @var PlanetFieldInterface[] $fields */
        $fields = $config($host, $selection);

        foreach ($fields as $field) {
            if ($field->isActive()) {
                continue;
            }
            $this->buildingAction->activate($field, $game);
        }

        $game->setView($host->getDefaultViewIdentifier());
        $game->setViewContext(ViewContextTypeEnum::COLONY_MENU, ColonyMenuEnum::MENU_BUILDINGS);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
