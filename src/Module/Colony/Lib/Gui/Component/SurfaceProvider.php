<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use Override;
use request;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;

final class SurfaceProvider implements GuiComponentProviderInterface
{
    public function __construct(private ColonyLibFactoryInterface $colonyLibFactory) {}

    #[Override]
    public function setTemplateVariables(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void {
        $game->setTemplateVar(
            'SURFACE',
            $this->colonyLibFactory->createColonySurface($host, request::getInt('buildingid') !== 0 ? request::getInt('buildingid') : null)
        );
    }
}
