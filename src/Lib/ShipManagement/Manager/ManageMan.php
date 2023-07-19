<?php

declare(strict_types=1);

namespace Stu\Lib\ShipManagement\Manager;

use RuntimeException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\ShipManagement\Provider\ManagerProviderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class ManageMan implements ManagerInterface
{
    private ShipSystemManagerInterface $shipSystemManager;

    public function __construct(ShipSystemManagerInterface $shipSystemManager)
    {
        $this->shipSystemManager = $shipSystemManager;
    }

    public function manage(ShipWrapperInterface $wrapper, array $values, ManagerProviderInterface $managerProvider): array
    {
        $msg = [];

        $man = $values['man'] ?? null;
        if ($man === null) {
            throw new RuntimeException('value array not existent');
        }

        $ship = $wrapper->get();
        $user = $managerProvider->getUser();
        $buildplan = $ship->getBuildplan();

        if (
            isset($man[$ship->getId()])
            && $ship->canMan()
            && $buildplan !== null
            && $ship->getUser() === $user
        ) {
            if ($buildplan->getCrew() > $managerProvider->getFreeCrewAmount()) {
                $msg[] = sprintf(
                    _('%s: Nicht genügend Crew auf der Kolonie vorhanden (%d benötigt)'),
                    $ship->getName(),
                    $buildplan->getCrew()
                );
            } else {
                $managerProvider->createShipCrew($ship);
                $msg[] = sprintf(
                    _('%s: Die Crew wurde hochgebeamt'),
                    $ship->getName()
                );

                if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)) {
                    $this->shipSystemManager->activate($wrapper, ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT, true);
                }
            }
        }

        return $msg;
    }
}
