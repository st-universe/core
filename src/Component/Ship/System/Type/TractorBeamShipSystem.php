<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class TractorBeamShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    private ShipRepositoryInterface $shipRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private EntityManagerInterface $entityManager;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        EntityManagerInterface $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipRepository = $shipRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->entityManager = $entityManager;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function checkActivationConditions(ShipInterface $ship, &$reason): bool
    {
        if ($ship->getCloakState()) {
            $reason = _('die Tarnung aktiviert ist');
            return false;
        }

        if ($ship->getShieldState()) {
            $reason = _('die Schilde aktiviert sind');
            return false;
        }

        if ($ship->getDockedTo()) {
            $reason = _('das Schiff angedockt ist');
            return false;
        }

        if ($ship->isTractored()) {
            $reason = sprintf(_('das Schiff selbst von dem Traktorstrahl der %s erfasst ist'), $ship->getTractoringShip()->getName());
            return false;
        }

        return true;
    }

    public function checkDeactivationConditions(ShipInterface $ship, &$reason): bool
    {
        if ($ship->getWarpState()) {
            $reason = _('der Warpantrieb aktiviert ist');
            return false;
        }

        return true;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 2;
    }

    public function getEnergyConsumption(): int
    {
        return 2;
    }

    public function activate(ShipInterface $ship, ShipSystemManagerInterface $manager): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM)->setMode(ShipSystemModeEnum::MODE_ON);
    }

    public function deactivate(ShipInterface $ship): void
    {
        $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM)->setMode(ShipSystemModeEnum::MODE_OFF);

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
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
                );
            }
        }
    }

    public function handleDestruction(ShipInterface $ship): void
    {
        if ($ship->isTractoring()) {
            $this->deactivate($ship);
        }
    }
}
