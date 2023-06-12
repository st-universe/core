<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Transwarp;

use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\MapRepositoryInterface;

final class Transwarp implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TRANSWARP';

    private ShipLoaderInterface $shipLoader;

    private MapRepositoryInterface $mapRepository;

    private ActivatorDeactivatorHelperInterface $helper;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        MapRepositoryInterface $mapRepository,
        ActivatorDeactivatorHelperInterface $helper
    ) {
        $this->shipLoader = $shipLoader;
        $this->mapRepository = $mapRepository;
        $this->helper = $helper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $shipId = request::postIntFatal('id');

        $ship = $this->shipLoader->getByIdAndUser(
            $shipId,
            $userId
        );

        $layerId = request::postIntFatal('transwarplayer');

        //sanity check if user knows layer
        if (!$game->getUser()->hasSeen($layerId)) {
            throw new SanityCheckException('user tried to access unseen layer');
        }

        //sanity checks
        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($ship->getSystem() !== null) {
            $game->addInformation(_('Transwarp kann nur außerhalb von Systemen genutzt werden'));
            return;
        }

        if (!$ship->getWarpState()) {
            $game->addInformation(_('Der Warpantrieb muss aktiviert sein'));
            return;
        }

        if ($ship->isTractored()) {
            $game->addInformation(_('Das Schiff wird von einem Traktorstrahl gehalten'));
            return;
        }

        if ($ship->isTractoring()) {
            $game->addInformation(_('Transwarpflug nicht möglich bei aktiviertem Traktorstrahl'));
            return;
        }

        if ($ship->getFleet() !== null) {
            $game->addInformation(_('Transwarpflug nicht möglich wenn Teil einer Flotte'));
            return;
        }

        // target check
        $cx = request::postInt('transwarpcx');
        $cy = request::postInt('transwarpcy');


        if (!$cx || !$cy || !$layerId) {
            $game->addInformation(_('Zielkoordinaten müssen angegeben werden'));
            return;
        }

        $map = $this->mapRepository->getByCoordinates($layerId, $cx, $cy);

        if ($map->getLayer()->isHidden()) {
            throw new SanityCheckException('tried to access hidden layer');
        }

        if ($map === null) {
            $game->addInformation(_('Zielkoordinaten existieren nicht'));
            return;
        }

        if (!$map->getFieldType()->getPassable()) {
            $game->addInformation(_('Zielkoordinaten können nicht angeflogen werden'));
            return;
        }

        $activated = $this->helper->activate($shipId, ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL, $game);

        if (!$activated) {
            return;
        }

        //flight happened
        $ship->updateLocation($map, null);
        $game->addInformation('Transwarpflug wurde durchgeführt');

        $this->helper->deactivate($shipId, ShipSystemTypeEnum::SYSTEM_TRANSWARP_COIL, $game);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
