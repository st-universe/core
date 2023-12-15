<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTorpedoTransfer;

use request;

use Stu\Component\Player\PlayerRelationDeterminatorInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowTorpedoTransfer implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TORP_TRANSFER';

    private ShipLoaderInterface $shipLoader;

    private PlayerRelationDeterminatorInterface $playerRelationDeterminator;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PlayerRelationDeterminatorInterface $playerRelationDeterminator
    ) {
        $this->shipLoader = $shipLoader;
        $this->playerRelationDeterminator = $playerRelationDeterminator;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::getIntFatal('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId,
            false,
            false
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();

        $game->setMacroInAjaxWindow('html/entityNotAvailable.twig');

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        if (!$ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)) {
            return;
        }
        if (!InteractionChecker::canInteractWith($ship, $target, $game, true)) {
            return;
        }

        $isUnload = request::has('isUnload');

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

        if (
            $target->getUser() !== $ship->getUser()
            && !$this->playerRelationDeterminator->isFriend($target->getUser(), $ship->getUser())
        ) {
            return;
        }

        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/show_torpedo_transfer');

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('target', $target);
        $game->setTemplateVar('MAXIMUM', $max);
        $game->setTemplateVar('IS_UNLOAD', $isUnload);
    }
}
