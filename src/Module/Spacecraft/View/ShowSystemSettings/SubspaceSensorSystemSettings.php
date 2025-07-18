<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSystemSettings;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemWrapperFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;


class SubspaceSensorSystemSettings implements SystemSettingsProviderInterface
{
    public function __construct(
        private readonly SpacecraftSystemWrapperFactoryInterface $spacecraftSystemWrapperFactory
    ) {}

    public function setTemplateVariables(
        SpacecraftSystemTypeEnum $systemType,
        SpacecraftWrapperInterface $wrapper,
        GameControllerInterface $game
    ): void {

        $game->setMacroInAjaxWindow('html/spacecraft/system/subspaceScanner.twig');

        $game->setTemplateVar(
            'systemWrapper',
            $this->spacecraftSystemWrapperFactory->create($wrapper->get(), $systemType)
        );
    }
}
