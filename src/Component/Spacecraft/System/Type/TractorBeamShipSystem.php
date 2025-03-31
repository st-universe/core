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
use Stu\Module\Spacecraft\Lib\SpacecraftNfsItem;
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

        if ($spacecraft->isCloaked()) {
            $reason = _('die Tarnung aktiviert ist');
            return false;
        }

        if ($spacecraft->isShielded()) {
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

        $tractoredShip = $spacecraft->getTractoredShip();
        if ($tractoredShip !== null) {

            $spacecraft->setTractoredShip(null);
            $this->spacecraftRepository->save($spacecraft);
            $this->entityManager->flush();
            $this->entityManager->refresh($spacecraft);

            $this->privateMessageSender->send(
                $spacecraft->getUser()->getId(),
                $tractoredShip->getUser()->getId(),
                sprintf(_('Der auf die %s gerichtete Traktorstrahl wurde in Sektor %s deaktiviert'), $tractoredShip->getName(), $spacecraft->getSectorString()),
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP
            );
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

    public static function isTractorBeamPossible(SpacecraftInterface|SpacecraftNfsItem $spacecraft): bool
    {
        return !($spacecraft->isStation()
            || $spacecraft->isCloaked()
            || $spacecraft->isShielded()
            || $spacecraft->isWarped());
    }
}
