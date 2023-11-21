<?php

namespace Stu\Module\Colony\Lib\Gui\Component;

use request;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;

final class SurfaceProvider implements GuiComponentProviderInterface
{
    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function setTemplateVariables(
        PlanetFieldHostInterface $host,
        GameControllerInterface $game
    ): void {
        $game->setTemplateVar(
            'SURFACE',
            $this->colonyLibFactory->createColonySurface($host, request::getInt('bid') !== 0 ? request::getInt('bid') : null)
        );
    }
}
