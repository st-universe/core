<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Strategy;

use request;
use Stu\Lib\Transfer\BeamUtilInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

class CommodityTransferStrategy implements TransferStrategyInterface
{
    private ColonyLibFactoryInterface $colonyLibFactory;

    private BeamUtilInterface $beamUtil;

    public function __construct(
        ColonyLibFactoryInterface $colonyLibFactory,
        BeamUtilInterface $beamUtil
    ) {
        $this->colonyLibFactory = $colonyLibFactory;
        $this->beamUtil = $beamUtil;
    }

    public function setTemplateVariables(
        bool $isUnload,
        ShipInterface $ship,
        ShipInterface|ColonyInterface $target,
        GameControllerInterface $game
    ): void {

        $game->setTemplateVar(
            'BEAMABLE_STORAGE',
            $isUnload ? $ship->getBeamableStorage() : $target->getBeamableStorage()
        );

        if ($target instanceof ColonyInterface) {
            $game->setTemplateVar(
                'SHOW_SHIELD_FREQUENCY',
                $this->colonyLibFactory->createColonyShieldingManager($target)->isShieldingEnabled() && $target->getUser() !== $ship->getUser()
            );
        }
    }

    public function transfer(
        bool $isUnload,
        ShipWrapperInterface $wrapper,
        ShipInterface|ColonyInterface $target,
        GameControllerInterface $game
    ): void {

        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');
        if (count($commodities) == 0 || count($gcount) == 0) {
            $game->addInformation(_("Es wurden keine Waren zum Beamen ausgewählt"));
            return;
        }

        $user = $game->getUser();

        if (
            $target instanceof ColonyInterface
            && $target->getUser() !== $user
            && $this->colonyLibFactory->createColonyShieldingManager($target)->isShieldingEnabled()
            && $target->getShieldFrequency() !== 0
        ) {
            $frequency = request::postInt('frequency');
            if ($frequency !== $target->getShieldFrequency()) {
                $game->addInformation(_("Die Schildfrequenz ist nicht korrekt"));
                return;
            }
        }

        // check for fleet option
        $fleetWrapper = $wrapper->getFleetWrapper();
        if (request::postInt('isfleet') && $fleetWrapper !== null) {
            foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
                $this->transferPerShip(
                    $isUnload,
                    $wrapper,
                    $target,
                    $game
                );
            }
        } else {
            $this->transferPerShip($isUnload, $wrapper, $target, $game);
        }
    }

    private function transferPerShip(
        bool $isUnload,
        ShipWrapperInterface $wrapper,
        ShipInterface|ColonyInterface $target,
        GameControllerInterface $game
    ): void {
        $ship = $wrapper->get();
        $epsSystem = $wrapper->getEpsSystemData();


        //sanity checks
        $isDockTransfer = $this->beamUtil->isDockTransfer($ship, $target);
        if (!$isDockTransfer && ($epsSystem === null || $epsSystem->getEps() === 0)) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->getWarpState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        if ($target instanceof ShipInterface && $target->getWarpState()) {
            $game->addInformation(sprintf(_('Die %s befindet sich im Warp'), $target->getName()));
            return;
        }

        $transferTarget = $isUnload ? $target : $ship;
        if ($transferTarget->getMaxStorage() <= $transferTarget->getStorageSum()) {
            $game->addInformation(sprintf(_('%s: Der Lagerraum ist voll'), $transferTarget->getName()));
            return;
        }

        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');

        $storage = $isUnload ? $ship->getStorage() : $target->getStorage();

        if ($storage->isEmpty()) {
            $game->addInformation(_("Keine Waren zum Beamen vorhanden"));
            return;
        }
        if (count($commodities) == 0 || count($gcount) == 0) {
            $game->addInformation(_("Es wurden keine Waren zum Beamen ausgewählt"));
            return;
        }
        $game->addInformation(sprintf(
            _('Die %s hat folgende Waren zur %s %s transferiert'),
            $ship->getName(),
            $target instanceof ColonyInterface ? 'Kolonie' : '',
            $target->getName()
        ));
        foreach ($commodities as $key => $value) {
            $commodityId = (int) $value;

            if (!array_key_exists($key, $gcount)) {
                continue;
            }

            $this->beamUtil->transferCommodity(
                $commodityId,
                $gcount[$key],
                $wrapper,
                $wrapper->get(),
                $target,
                $game
            );
        }

        $game->sendInformation(
            $target->getUser()->getId(),
            $ship->getUser()->getId(),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            sprintf(
                '%s.php?%s=1&id=%d',
                $target instanceof ShipInterface ? 'ship' : 'colony',
                $target instanceof ShipInterface ? ShowShip::VIEW_IDENTIFIER : ShowColony::VIEW_IDENTIFIER,
                $target->getId()
            )
        );
    }
}
