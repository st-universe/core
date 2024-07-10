<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowWebEmitter;

use Override;
use request;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class ShowWebEmitter implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_WEBEMITTER_AJAX';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private ShipRepositoryInterface $shipRepository,
        private TholianWebRepositoryInterface $tholianWebRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );
        $ship = $wrapper->get();

        $game->setPageTitle(_('Webemitter'));
        $game->setMacroInAjaxWindow('html/ship/webemitter.twig');

        $game->setTemplateVar('WRAPPER', $wrapper);

        $emitter = $wrapper->getWebEmitterSystemData();
        if ($emitter === null) {
            throw new SanityCheckException('no web emitter installed', null, self::VIEW_IDENTIFIER);
        }


        $webUnderConstruction = $emitter->getWebUnderConstruction();
        $ownWeb = $emitter->getOwnedTholianWeb();

        if ($ownWeb !== null && $ownWeb->isFinished()) {
            $game->setTemplateVar('OWNFINISHEDWEB', $ownWeb);
        }

        //helping under construction?
        if ($webUnderConstruction !== null && !$webUnderConstruction->isFinished()) {
            $this->loggerUtil->log('A');
            $game->setTemplateVar('WEBCONSTRUCT', $webUnderConstruction);
            $game->setTemplateVar('ISOWNCONSTRUCT', $webUnderConstruction === $ownWeb);
            return;
        }

        $web = $this->tholianWebRepository->getWebAtLocation($ship);

        if ($web === null) {
            $this->loggerUtil->log('B');
            // wenn keines da und isUseable -> dann Targetliste
            if ($emitter->isUseable()) {
                $this->loggerUtil->log('C');
                $possibleTargetList =
                    array_filter(
                        $this->shipRepository->getByLocation($ship->getCurrentMapField()),
                        fn (ShipInterface $target): bool => !$target->getCloakState() && !$target->isWarped() && $target !== $ship
                    );

                $game->setTemplateVar('AVAILABLE_SHIPS', $possibleTargetList);
            } else {
                $this->loggerUtil->log('D');
                $game->setTemplateVar('COOLDOWN', $emitter->getCooldown());
            }
        } else {
            $this->loggerUtil->log('E');

            //can help under construction?
            //fremdes Netz under construction da? -> dann button für Support
            if (!$web->isFinished()) {
                $this->loggerUtil->log('F');
                $game->setTemplateVar('CANHELP', true);
            } else {
                $this->loggerUtil->log('G');
                $game->setTemplateVar('OWNFINISHED', $web->getUser() === $user);
            }
        }
    }
}
