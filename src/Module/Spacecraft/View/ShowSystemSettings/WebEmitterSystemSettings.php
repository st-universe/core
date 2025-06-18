<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSystemSettings;

use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

class WebEmitterSystemSettings implements SystemSettingsProviderInterface
{
    public function __construct(
        private TholianWebRepositoryInterface $tholianWebRepository
    ) {}

    public function setTemplateVariables(
        SpacecraftSystemTypeEnum $systemType,
        SpacecraftWrapperInterface $wrapper,
        GameControllerInterface $game
    ): void {

        if (!$wrapper instanceof ShipWrapperInterface) {
            throw new RuntimeException('this should not happen');
        }

        $user = $game->getUser();
        $ship = $wrapper->get();

        $game->setMacroInAjaxWindow('html/ship/webemitter.twig');

        $emitter = $wrapper->getWebEmitterSystemData();
        if ($emitter === null) {
            throw new SanityCheckException('no web emitter installed', null, ShowSystemSettings::VIEW_IDENTIFIER);
        }

        $webUnderConstruction = $emitter->getWebUnderConstruction();
        $ownWeb = $emitter->getOwnedTholianWeb();

        if ($ownWeb !== null && $ownWeb->isFinished()) {
            $game->setTemplateVar('OWNFINISHEDWEB', $ownWeb);
        }

        //helping under construction?
        if ($webUnderConstruction !== null && !$webUnderConstruction->isFinished()) {
            $game->setTemplateVar('WEBCONSTRUCT', $webUnderConstruction);
            $game->setTemplateVar('ISOWNCONSTRUCT', $webUnderConstruction === $ownWeb);
            return;
        }

        $web = $this->tholianWebRepository->getWebAtLocation($ship);

        if ($web === null) {
            // wenn keines da und isUseable -> dann Targetliste
            if ($emitter->isUseable()) {
                $possibleTargetList = $ship->getLocation()
                    ->getSpacecraftsWithoutVacation()
                    ->filter(fn(SpacecraftInterface $target): bool => !$target->isCloaked() && !$target->isWarped() && $target !== $ship);

                $game->setTemplateVar('AVAILABLE_SHIPS', $possibleTargetList);
            } else {
                $game->setTemplateVar('COOLDOWN', $emitter->getCooldown());
            }
        } else {

            //can help under construction?
            //fremdes Netz under construction da? -> dann button für Support
            if (!$web->isFinished()) {
                $game->setTemplateVar('CANHELP', true);
            } else {
                $game->setTemplateVar('OWNFINISHED', $web->getUser() === $user);
            }
        }
    }
}
