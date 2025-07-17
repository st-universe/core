<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSystemSettings;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;


class SubspaceSensorSystemSettings implements SystemSettingsProviderInterface
{
    public function __construct() {}

    public function setTemplateVariables(
        SpacecraftSystemTypeEnum $systemType,
        SpacecraftWrapperInterface $wrapper,
        GameControllerInterface $game
    ): void {

        $game->setMacroInAjaxWindow('html/ship/subspacescanner.twig');
    }
}
