<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSystemSettings;

use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\LocationMiningRepositoryInterface;

class BussardCollectorSystemSettings implements SystemSettingsProviderInterface
{
    public function __construct(
        private LocationMiningRepositoryInterface $locationMiningRepository
    ) {}

    public function setTemplateVariables(
        SpacecraftSystemTypeEnum $systemType,
        SpacecraftWrapperInterface $wrapper,
        GameControllerInterface $game
    ): void {

        if (!$wrapper instanceof ShipWrapperInterface) {
            throw new RuntimeException('this should not happen');
        }

        $ship = $wrapper->get();

        $game->setMacroInAjaxWindow('html/ship/bussardcollector.twig');

        $collector = $wrapper->getBussardCollectorSystemData();
        if ($collector === null) {
            throw new SanityCheckException('no bussard collector installed', null, ShowSystemSettings::VIEW_IDENTIFIER);
        }

        $mining = $this->locationMiningRepository->getMiningAtLocation($ship);
        $miningqueue = $ship->getMiningQueue();
        $game->setTemplateVar('MINING', $mining);
        $game->setTemplateVar('MININGQUEUE', $miningqueue);
    }
}
