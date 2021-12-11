<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTorpedoTransfer;

use request;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowTorpedoTransfer implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TORP_TRANSFER';

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $user->getId()
        );

        if (!$ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)) {
            return;
        }

        $isUnload = request::has('isUnload');

        $target = $this->shipLoader->find((int) request::getIntFatal('target'));

        if ($isUnload) {
            $max = min(
                $target->getMaxTorpedos() - $target->getTorpedoCount(),
                $ship->getTorpedoCount()
            );
            $game->setPageTitle(_('Schiff mit Torpedos ausrüsten'));
        } else {
            $max = min(
                $ship->getMaxTorpedos() - $ship->getTorpedoCount(),
                $target->getTorpedoCount()
            );
            $game->setPageTitle(_('Torpedos von Schiff beamen'));
        }

        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/shipmacros.xhtml/entity_not_available');

        if ($target === null || !$ship->canInteractWith($target, false, true)) {
            return;
        }

        if ($target->getUser() !== $ship->getUser()) {
            return;
        }

        $game->setMacro('html/shipmacros.xhtml/show_torpedo_transfer');

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('target', $target);
        $game->setTemplateVar('MAXIMUM', $max);
        $game->setTemplateVar('IS_UNLOAD', $isUnload);
    }
}
