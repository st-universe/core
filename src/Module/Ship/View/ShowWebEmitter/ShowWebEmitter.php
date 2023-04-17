<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowWebEmitter;

use request;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class ShowWebEmitter implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_WEBEMITTER_AJAX';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private TholianWebRepositoryInterface $tholianWebRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        TholianWebRepositoryInterface $tholianWebRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->tholianWebRepository = $tholianWebRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        if ($userId === 126) {
            //$this->loggerUtil->init('WEB', LoggerEnum::LEVEL_WARNING);
        }

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        $game->setPageTitle(_('Webemitter'));
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/webemitter');

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
                        $this->shipRepository->getByLocation(
                            $ship->getStarsystemMap(),
                            $ship->getMap()
                        ),
                        function (ShipInterface $target) use ($ship): bool {
                            return !$target->getCloakState() && !$target->getWarpState() && $target !== $ship;
                        }
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
