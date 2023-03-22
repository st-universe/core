<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class FightLib implements FightLibInterface
{
    private ShipSystemManagerInterface $shipSystemManager;

    private CancelRepairInterface $cancelRepair;

    private AlertLevelBasedReactionInterface $alertLevelBasedReaction;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager,
        CancelRepairInterface $cancelRepair,
        AlertLevelBasedReactionInterface $alertLevelBasedReaction
    ) {
        $this->shipSystemManager = $shipSystemManager;
        $this->cancelRepair = $cancelRepair;
        $this->alertLevelBasedReaction = $alertLevelBasedReaction;
    }

    public function ready(ShipWrapperInterface $wrapper): array
    {
        $ship = $wrapper->get();

        if (
            $ship->isDestroyed()
            || $ship->getRump()->isEscapePods()
        ) {
            return [];
        }
        if ($ship->getBuildplan() === null) {
            return [];
        }
        if (!$ship->hasEnoughCrew()) {
            return [];
        }

        $msg = [];
        if ($ship->getDockedTo() !== null) {
            $ship->setDockedTo(null);
            $msg[] = "- Das Schiff hat abgedockt";
        }

        try {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
        } catch (ShipSystemException $e) {
        }
        try {
            $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_CLOAK);
        } catch (ShipSystemException $e) {
        }

        $this->cancelRepair->cancelRepair($ship);

        $msg = array_merge($msg, $this->alertLevelBasedReaction->react($wrapper));

        if ($msg !== []) {
            $msg = array_merge([sprintf(_('Aktionen der %s'), $ship->getName())], $msg);
        }

        return $msg;
    }

    public function filterInactiveShips(array $base): array
    {
        return array_filter(
            $base,
            function (ShipWrapperInterface $wrapper): bool {
                return !$wrapper->get()->isDestroyed() && !$wrapper->get()->isDisabled();
            }
        );
    }

    public function canFire(ShipWrapperInterface $wrapper): bool
    {
        $ship = $wrapper->get();
        if (!$ship->getNbs()) {
            return false;
        }
        if (!$ship->hasActiveWeapon()) {
            return false;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() === 0) {
            return false;
        }

        return true;
    }
}
