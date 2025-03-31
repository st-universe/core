<?php

declare(strict_types=1);

namespace Stu\Lib\SpacecraftManagement\Manager;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Lib\SpacecraftManagement\Provider\ManagerProviderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;

class ManageBattery implements ManagerInterface
{
    public function __construct(
        private PrivateMessageSenderInterface $privateMessageSender,
        private PlayerRelationDeterminatorInterface $playerRelationDeterminator
    ) {}

    #[Override]
    public function manage(SpacecraftWrapperInterface $wrapper, array $values, ManagerProviderInterface $managerProvider): array
    {
        $msg = [];

        $batt = $values['batt'] ?? null;
        if ($batt === null) {
            throw new RuntimeException('value array not existent');
        }

        $ship = $wrapper->get();
        $shipId = $ship->getId();
        $epsSystem = $wrapper->getEpsSystemData();

        if (
            $epsSystem === null
            || !array_key_exists($shipId, $batt)
            || $batt[$shipId] === ''
        ) {
            return $msg;
        }

        if ($ship->isShielded() && !$this->playerRelationDeterminator->isFriend($ship->getUser(), $managerProvider->getUser())) {
            $msg[] = sprintf(
                _('%s: Batterie konnte wegen aktivierter Schilde nicht aufgeladen werden.'),
                $ship->getName()
            );
            return $msg;
        }

        if (
            $managerProvider->getEps() > 0
            && $epsSystem->getBattery() < $epsSystem->getMaxBattery()
        ) {
            $load = $this->determineLoad($batt[$shipId], $epsSystem, $managerProvider);

            if ($load > 0) {
                $epsSystem->setBattery($epsSystem->getBattery() + $load)->update();
                $managerProvider->lowerEps($load);
                $msg[] = sprintf(
                    _('%s: Batterie um %d Einheiten aufgeladen'),
                    $ship->getName(),
                    $load
                );

                $this->sendMessageToOwner($ship, $managerProvider, $load);
            }
        }

        return $msg;
    }

    private function determineLoad(
        string $value,
        EpsSystemData $epsSystem,
        ManagerProviderInterface $managerProvider
    ): int {
        if ($value === 'm') {
            $load = $epsSystem->getMaxBattery() - $epsSystem->getBattery();
        } else {
            $load = (int) $value;
            if ($epsSystem->getBattery() + $load > $epsSystem->getMaxBattery()) {
                $load = $epsSystem->getMaxBattery() - $epsSystem->getBattery();
            }
        }
        if ($load > $managerProvider->getEps()) {
            $load = $managerProvider->getEps();
        }

        return $load;
    }

    private function sendMessageToOwner(SpacecraftInterface $spacecraft, ManagerProviderInterface $managerProvider, int $load): void
    {
        $this->privateMessageSender->send(
            $managerProvider->getUser()->getId(),
            $spacecraft->getUser()->getId(),
            sprintf(
                _('Die %s lädt in Sektor %s die Batterie der %s um %s Einheiten'),
                $managerProvider->getName(),
                $managerProvider->getSectorString(),
                $spacecraft->getName(),
                $load
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_TRADE,
            $spacecraft
        );
    }
}
