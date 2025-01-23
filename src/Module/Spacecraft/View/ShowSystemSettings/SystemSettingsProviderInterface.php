<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSystemSettings;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface SystemSettingsProviderInterface
{
    public function setTemplateVariables(
        SpacecraftSystemTypeEnum $systemType,
        SpacecraftWrapperInterface $wrapper,
        GameControllerInterface $game
    ): void;
}
