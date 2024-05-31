<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Strategy;

use request;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Lib\Transfer\BeamUtilInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

class CommodityTransferStrategy implements TransferStrategyInterface
{
    public function __construct(
        private ColonyLibFactoryInterface $colonyLibFactory,
        private BeamUtilInterface $beamUtil,
        private PirateReactionInterface $pirateReaction
    ) {
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
        InformationWrapper $informations
    ): void {

        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');
        if (count($commodities) == 0 || count($gcount) == 0) {
            $informations->addInformation(_("Es wurden keine Waren zum Beamen ausgewählt"));
            return;
        }

        $user = $wrapper->get()->getUser();

        if (
            $target instanceof ColonyInterface
            && $target->getUser() !== $user
            && $this->colonyLibFactory->createColonyShieldingManager($target)->isShieldingEnabled()
            && $target->getShieldFrequency() !== 0
        ) {
            $frequency = request::postInt('frequency');
            if ($frequency !== $target->getShieldFrequency()) {
                $informations->addInformation(_("Die Schildfrequenz ist nicht korrekt"));
                return;
            }
        }

        $hasTransfered = false;

        // check for fleet option
        $fleetWrapper = $wrapper->getFleetWrapper();
        if (request::postInt('isfleet') && $fleetWrapper !== null) {
            foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
                $this->transferPerShip(
                    $isUnload,
                    $wrapper,
                    $target,
                    $informations
                ) || $hasTransfered = $hasTransfered;
            }
        } else {
            $hasTransfered =  $this->transferPerShip($isUnload, $wrapper, $target, $informations);
        }

        if (
            !$isUnload
            && $hasTransfered
            && $target instanceof ShipInterface
        ) {
            $this->pirateReaction->checkForPirateReaction(
                $target,
                PirateReactionTriggerEnum::ON_BEAM,
                $wrapper->get()
            );
        }
    }

    private function transferPerShip(
        bool $isUnload,
        ShipWrapperInterface $wrapper,
        ShipInterface|ColonyInterface $target,
        InformationWrapper $informations
    ): bool {

        $ship = $wrapper->get();
        $epsSystem = $wrapper->getEpsSystemData();

        //sanity checks
        $isDockTransfer = $this->beamUtil->isDockTransfer($ship, $target);
        if (!$isDockTransfer && ($epsSystem === null || $epsSystem->getEps() === 0)) {
            $informations->addInformation(_("Keine Energie vorhanden"));
            return false;
        }
        if ($ship->getCloakState()) {
            $informations->addInformation(_("Die Tarnung ist aktiviert"));
            return false;
        }
        if ($ship->isWarped()) {
            $informations->addInformation("Schiff befindet sich im Warp");
            return false;
        }
        if ($target instanceof ShipInterface && $target->isWarped()) {
            $informations->addInformation(sprintf(_('Die %s befindet sich im Warp'), $target->getName()));
            return false;
        }

        $transferTarget = $isUnload ? $target : $ship;
        if ($transferTarget->getMaxStorage() <= $transferTarget->getStorageSum()) {
            $informations->addInformation(sprintf(_('%s: Der Lagerraum ist voll'), $transferTarget->getName()));
            return false;
        }

        $commodities = request::postArray('commodities');
        $gcount = request::postArray('count');

        $storage = $isUnload ? $ship->getStorage() : $target->getStorage();

        if ($storage->isEmpty()) {
            $informations->addInformation(_("Keine Waren zum Beamen vorhanden"));
            return false;
        }
        if (count($commodities) == 0 || count($gcount) == 0) {
            $informations->addInformation(_("Es wurden keine Waren zum Beamen ausgewählt"));
            return false;
        }
        $informations->addInformation(sprintf(
            _('Die %s hat folgende Waren %s %s %s transferiert'),
            $ship->getName(),
            $isUnload ? 'zur' : 'von der',
            $target instanceof ColonyInterface ? 'Kolonie' : '',
            $target->getName()
        ));

        $hasTransfered = false;
        foreach ($commodities as $key => $value) {
            $commodityId = (int) $value;

            if (!array_key_exists($key, $gcount)) {
                continue;
            }

            $this->beamUtil->transferCommodity(
                $commodityId,
                $gcount[$key],
                $wrapper,
                $isUnload ? $ship : $target,
                $transferTarget,
                $informations
            ) || $hasTransfered = $hasTransfered;
        }

        return $hasTransfered;
    }
}
