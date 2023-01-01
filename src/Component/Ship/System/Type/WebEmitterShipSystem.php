<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Type;

use Stu\Component\Game\TimeConstants;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

class WebEmitterShipSystem extends AbstractShipSystemType implements ShipSystemTypeInterface
{
    private ShipSystemRepositoryInterface $shipSystemRepository;

    private ShipRepositoryInterface $shipRepository;

    private TholianWebRepositoryInterface $tholianWebRepository;

    public function __construct(
        ShipSystemRepositoryInterface $shipSystemRepository,
        ShipRepositoryInterface $shipRepository,
        TholianWebRepositoryInterface $tholianWebRepository
    ) {
        $this->shipSystemRepository = $shipSystemRepository;
        $this->shipRepository = $shipRepository;
        $this->tholianWebRepository = $tholianWebRepository;
    }

    public function getSystemType(): int
    {
        return ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB;
    }

    public function deactivate(ShipWrapperInterface $wrapper): void
    {
        $this->checkForWebAbortion($wrapper);
        $wrapper->get()->getShipSystem($this->getSystemType())->setMode(ShipSystemModeEnum::MODE_OFF);
    }

    public function getCooldownSeconds(): ?int
    {
        return TimeConstants::ONE_DAY_IN_SECONDS;
    }

    public function getEnergyUsageForActivation(): int
    {
        return 30;
    }

    public function getEnergyConsumption(): int
    {
        return 10;
    }

    public function handleDestruction(ShipWrapperInterface $wrapper): void
    {
        $this->checkForWebAbortion($wrapper);
    }

    private function checkForWebAbortion(ShipWrapperInterface $wrapper): void
    {
        $webUnderConstruction = $wrapper->getWebEmitterSystemData()->getWebUnderConstruction();

        if ($webUnderConstruction === null) {
            return;
        }

        $systems = $this->shipSystemRepository->getWebConstructingShipSystems($webUnderConstruction->getId());
        $emitter = $wrapper->getWebEmitterSystemData();

        //remove web if only one ship constructing
        if (count($systems) === 1) {
            //unlink targets
            foreach ($webUnderConstruction->getCapturedShips() as $target) {
                $target->setHoldingWeb(null);
                $this->shipRepository->save($target);
            }
            $webUnderConstruction->getCapturedShips()->clear();

            //delete web ship
            $this->tholianWebRepository->delete($webUnderConstruction);
            $this->shipRepository->delete($webUnderConstruction->getWebShip());

            if ($emitter->ownedWebId === $emitter->webUnderConstructionId) {
                $emitter->setOwnedWebId(null);
            }
        }

        $emitter->setWebUnderConstructionId(null)->update();
    }
}
