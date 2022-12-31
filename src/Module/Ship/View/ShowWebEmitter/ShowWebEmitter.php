<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowWebEmitter;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class ShowWebEmitter implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_WEBEMITTER_AJAX';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private TholianWebRepositoryInterface $tholianWebRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        TholianWebRepositoryInterface $tholianWebRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->tholianWebRepository = $tholianWebRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        $game->setPageTitle(_('Webemitter'));
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/webemitter');

        $game->setTemplateVar('WRAPPER', $wrapper);

        $emitter = $wrapper->getWebEmitterSystemData();
        $webUnderConstruction = $emitter->getWebUnderConstruction();
        $ownWeb = $emitter->getOwnedTholianWeb();

        //helping under construction?
        if ($webUnderConstruction !== null) {
            $game->setTemplateVar('WEBCONSTRUCT', $webUnderConstruction);
            $game->setTemplateVar('ISOWNCONSTRUCT', $webUnderConstruction === $ownWeb);
        }

        $web = $this->tholianWebRepository->getWebAtLocation($ship);

        if ($web === null) {

            // wenn keines da und isUseable -> dann Targetliste
            if ($emitter->isUseable()) {
                $game->setTemplateVar('SHIPLIST', $this->shipRepository->getByLocation(
                    $ship->getStarsystemMap(),
                    $ship->getMap()
                ));
            } else {
                $game->setTemplateVar('COOLDOWN', $emitter->getCooldown());
            }
        }

        //can help under construction?
        //fremdes Netz under construction da? -> dann button für Support
        if (!$web->isFinished()) {
            $game->setTemplateVar('CANHELP', true);
        } else {
            $game->setTemplateVar('OWNFINISHED', $web->getUser() === $user);
        }
    }
}
