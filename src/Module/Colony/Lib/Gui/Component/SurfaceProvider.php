<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use request;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;

final class SurfaceProvider implements PlanetFieldHostComponentInterface
{
    public function __construct(private ColonyLibFactoryInterface $colonyLibFactory) {}

    #[Override]
    public function setTemplateVariables(
        $entity,
        GameControllerInterface $game
    ): void {
        $game->setTemplateVar(
            'SURFACE',
            $this->colonyLibFactory->createColonySurface($entity, request::getInt('buildingid') !== 0 ? request::getInt('buildingid') : null)
        );
    }
}
