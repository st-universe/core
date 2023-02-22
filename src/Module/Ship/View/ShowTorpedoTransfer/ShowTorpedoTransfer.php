<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTorpedoTransfer;

use request;

use Stu\Component\Player\PlayerRelationDeterminatorInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowTorpedoTransfer implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TORP_TRANSFER';

    private ShipLoaderInterface $shipLoader;

    private LoggerUtilInterface $loggerUtil;

    private PlayerRelationDeterminatorInterface $playerRelationDeterminator;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PlayerRelationDeterminatorInterface $playerRelationDeterminator,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->playerRelationDeterminator = $playerRelationDeterminator;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::getIntFatal('target');

        $shipArray = $this->shipLoader->getWrappersByIdAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $shipArray[$shipId];
        $ship = $wrapper->get();

        $targetWrapper = $shipArray[$targetId];
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        if (!$ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)) {
            return;
        }
        if (!InteractionChecker::canInteractWith($ship, $target, $game, false, true)) {
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

        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/entity_not_available');


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
