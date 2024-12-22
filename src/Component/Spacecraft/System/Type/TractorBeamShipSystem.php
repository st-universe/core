<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\ShipNfsItem;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class TractorBeamShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private EntityManagerInterface $entityManager
    ) {}

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::TRACTOR_BEAM;
    }

    #[Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        $spacecraft = $wrapper->get();

        if ($spacecraft->getCloakState()) {
            $reason = _('die Tarnung aktiviert ist');
            return false;
        }

        if ($spacecraft->getShieldState()) {
            $reason = _('die Schilde aktiviert sind');
            return false;
        }

        if ($spacecraft instanceof ShipInterface) {
            if ($spacecraft->getDockedTo() !== null) {
                $reason = _('das Schiff angedockt ist');
                return false;
            }

            $tractoringSpacecraft = $spacecraft->getTractoringSpacecraft();
            if ($tractoringSpacecraft !== null) {
                $reason = sprintf(
                    _('das Schiff selbst von dem Traktorstrahl der %s erfasst ist'),
                    $tractoringSpacecraft->getName()
                );
                return false;
            }
        }

        return true;
    }

    #[Override]
    public function checkDeactivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
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
    public function deactivate(SpacecraftWrapperInterface $wrapper): void
    {
        $spacecraft = $wrapper->get();

        $spacecraft->getSpacecraftSystem($this->getSystemType())->setMode(SpacecraftSystemModeEnum::MODE_OFF);

        if ($spacecraft->isTractoring()) {
            $traktor = $spacecraft->getTractoredShip();

            $spacecraft->setTractoredShip(null);
            $spacecraft->setTractoredShipId(null);
            $this->spacecraftRepository->save($spacecraft);
            $this->entityManager->flush();

            if ($traktor !== null) {
                $this->privateMessageSender->send(
                    $spacecraft->getUser()->getId(),
                    $traktor->getUser()->getId(),
                    sprintf(_('Der auf die %s gerichtete Traktorstrahl wurde in Sektor %s deaktiviert'), $traktor->getName(), $spacecraft->getSectorString()),
                    PrivateMessageFolderTypeEnum::SPECIAL_SHIP
                );
            }
        }
    }

    #[Override]
    public function handleDestruction(SpacecraftWrapperInterface $wrapper): void
    {
        $spacecraft = $wrapper->get();
        if ($spacecraft->isTractoring()) {
            $this->deactivate($wrapper);
        }
    }

    public static function isTractorBeamPossible(SpacecraftInterface|ShipNfsItem $spacecraft): bool
    {
        return !($spacecraft->isStation()
            || $spacecraft->getCloakState()
            || $spacecraft->getShieldState()
            || $spacecraft->isWarped());
    }
}
