<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipNfsItem;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class TractorBeamShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    public function __construct(private ShipRepositoryInterface $shipRepository, private PrivateMessageSenderInterface $privateMessageSender, private EntityManagerInterface $entityManager)
    {
    }

    #[Override]
    public function getSystemType(): ShipSystemTypeEnum
    {
        return ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM;
    }

    #[Override]
    public function checkActivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        $ship = $wrapper->get();

        if ($ship->getCloakState()) {
            $reason = _('die Tarnung aktiviert ist');
            return false;
        }

        if ($ship->getShieldState()) {
            $reason = _('die Schilde aktiviert sind');
            return false;
        }

        if ($ship->getDockedTo() !== null) {
            $reason = _('das Schiff angedockt ist');
            return false;
        }

        if ($ship->isTractored()) {
            $reason = sprintf(_('das Schiff selbst von dem Traktorstrahl der %s erfasst ist'), $ship->getTractoringShip()->getName());
            return false;
        }

        return true;
    }

    #[Override]
    public function checkDeactivationConditions(ShipWrapperInterface $wrapper, string &$reason): bool
    {
        if ($wrapper->get()->getWarpDriveState()) {
            $reason = _('der Warpantrieb aktiviert ist');
            return false;
        }

        return true;
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 2;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 2;
    }

    #[Override]
    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();

        $ship->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_OFF);

        if ($ship->isTractoring()) {
            $traktor = $ship->getTractoredShip();

            $ship->setTractoredShip(null);
            $ship->setTractoredShipId(null);
            $this->shipRepository->save($ship);
            $this->entityManager->flush();

            if ($traktor !== null) {
                $this->privateMessageSender->send(
                    $ship->getUser()->getId(),
                    $traktor->getUser()->getId(),
                    sprintf(_('Der auf die %s gerichtete Traktorstrahl wurde in Sektor %s deaktiviert'), $traktor->getName(), $ship->getSectorString()),
                    PrivateMessageFolderTypeEnum::SPECIAL_SHIP
                );
            }
        }
    }

    #[Override]
    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        if ($ship->isTractoring()) {
            $this->deactivate($wrapper);
        }
    }

    public static function isTractorBeamPossible(ShipInterface|ShipNfsItem $ship): bool
    {
        return !($ship->isBase()
            || $ship->isTrumfield()
            || $ship->getCloakState()
            || $ship->getShieldState()
            || $ship->isWarped());
    }
}
