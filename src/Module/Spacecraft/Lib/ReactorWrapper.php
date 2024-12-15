<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Override;
use Stu\Component\Spacecraft\System\Data\AbstractReactorSystemData;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ReactorWrapper implements ReactorWrapperInterface
{
    //absolute values
    private ?int $epsProduction = null;
    private ?int $warpdriveProduction = null;

    //effective values
    private ?int $effectiveEpsProduction = null;
    private ?int $effectiveWarpDriveProduction = null;

    public function __construct(private SpacecraftWrapperInterface $wrapper, private AbstractReactorSystemData $reactorSystemData) {}

    #[Override]
    public function get(): AbstractReactorSystemData
    {
        return $this->reactorSystemData;
    }

    #[Override]
    public function getEpsProduction(): int
    {
        if ($this->epsProduction === null) {
            $warpdrive = $this->wrapper->getWarpDriveSystemData();
            $warpdriveSplit = $warpdrive === null ? 100 : $warpdrive->getWarpDriveSplit();
            $reactorOutput = $this->getOutputCappedByLoad();

            if ($warpdriveSplit === 0) {
                $this->epsProduction = min($reactorOutput, $this->wrapper->getEpsUsage());
            } else {
                $warpDriveProduction = $this->getWarpdriveProduction();
                $flightCost = $this->wrapper->get()->getRump()->getFlightEcost();

                $this->epsProduction = $reactorOutput - ($warpDriveProduction * $flightCost);
            }
        }

        return $this->epsProduction;
    }

    private function getWarpdriveProduction(): int
    {
        if ($this->warpdriveProduction === null) {
            $warpdrive = $this->wrapper->getWarpDriveSystemData();

            if ($warpdrive === null) {
                $this->warpdriveProduction = 0;
            } else {
                $warpdriveSplit = $warpdrive->getWarpDriveSplit();
                $reactorOutput = $this->getOutputCappedByLoad();
                $flightCost = $this->wrapper->get()->getRump()->getFlightEcost();
                $maxWarpdriveGain = max(0, (int)floor(($reactorOutput - $this->wrapper->getEpsUsage()) / $flightCost));

                $this->warpdriveProduction = (int)round((1 - ($warpdriveSplit / 100)) * $maxWarpdriveGain);
            }
        }

        return $this->warpdriveProduction;
    }

    #[Override]
    public function getEffectiveEpsProduction(): int
    {
        if ($this->effectiveEpsProduction === null) {
            $this->calculateEffectiveProduction();
        }
        return $this->effectiveEpsProduction;
    }

    #[Override]
    public function getEffectiveWarpDriveProduction(): int
    {
        if ($this->effectiveWarpDriveProduction === null) {
            $this->calculateEffectiveProduction();
        }

        return $this->effectiveWarpDriveProduction;
    }

    private function calculateEffectiveProduction(): void
    {
        $epsSystem = $this->wrapper->getEpsSystemData();
        $warpdrive = $this->wrapper->getWarpDriveSystemData();

        $missingEps = $epsSystem === null ? 0 : $epsSystem->getMaxEps() - $epsSystem->getEps();
        $missingWarpdrive = $warpdrive === null ? 0 : $warpdrive->getMaxWarpDrive() - $warpdrive->getWarpDrive();

        $potential = $this->getOutputCappedByLoad();
        $potential -= $this->wrapper->getEpsUsage();

        $flightCost = $this->wrapper->get()->getRump()->getFlightEcost();

        $epsChange = $this->getEpsProduction() - $this->wrapper->getEpsUsage();
        $effEpsProd = min($missingEps, $epsChange);
        $effWdProd = min($missingWarpdrive, $this->getWarpdriveProduction());

        if ($warpdrive !== null && $warpdrive->getAutoCarryOver()) {
            $excess = max(0, $potential - $effEpsProd - $effWdProd * $flightCost);
            $epsChange = $this->getEpsProduction() + $excess - $this->wrapper->getEpsUsage();

            $effEpsProd = min($missingEps, $epsChange);
            $effWdProd = min($missingWarpdrive, $this->getWarpdriveProduction() + (int)floor($excess / $flightCost));
        }

        $this->effectiveEpsProduction = $effEpsProd;
        $this->effectiveWarpDriveProduction = $effWdProd;
    }

    #[Override]
    public function getUsage(): int
    {
        return $this->getEffectiveEpsProduction()
            + $this->getEffectiveWarpDriveProduction() * $this->wrapper->get()->getRump()->getFlightEcost();
    }

    #[Override]
    public function getCapacity(): int
    {
        return $this->reactorSystemData->getCapacity();
    }

    #[Override]
    public function getOutput(): int
    {
        return $this->reactorSystemData->getOutput();
    }

    #[Override]
    public function setOutput(int $output): ReactorWrapperInterface
    {
        $this->reactorSystemData->setOutput($output)->update();

        return $this;
    }

    #[Override]
    public function getOutputCappedByLoad(): int
    {
        if ($this->getOutput() > $this->getLoad()) {
            return $this->getLoad();
        }

        return $this->getOutput();
    }

    #[Override]
    public function getLoad(): int
    {
        return $this->reactorSystemData->getLoad();
    }

    #[Override]
    public function setLoad(int $load): ReactorWrapperInterface
    {
        $this->reactorSystemData->setLoad($load)->update();

        return $this;
    }

    #[Override]
    public function changeLoad(int $amount): ReactorWrapperInterface
    {
        $this->reactorSystemData->setLoad($this->reactorSystemData->getLoad() + $amount)->update();

        return $this;
    }

    #[Override]
    public function isHealthy(): bool
    {
        return $this->wrapper->get()->isSystemHealthy($this->reactorSystemData->getSystemType());
    }

    #[Override]
    public function getReactorLoadStyle(): string
    {
        $load = $this->getLoad();
        $output = $this->getOutput();

        if ($load < $output) {
            return "color: red;";
        }

        if ($this->getCapacity() === 0) {
            return "";
        }

        $percentage = $load / $this->getCapacity();

        return $percentage > 0.3 ? "" : "color: yellow;";
    }
}
