<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\System\Data\AbstractReactorSystemData;

final class ReactorWrapper implements ReactorWrapperInterface
{
    private ShipWrapperInterface $wrapper;
    private AbstractReactorSystemData $reactorSystemData;

    //absolute values
    private ?int $epsProduction = null;
    private ?int $warpdriveProduction = null;

    //effective values    
    private ?int $effectiveEpsProduction = null;
    private ?int $effectiveWarpDriveProduction = null;

    public function __construct(
        ShipWrapperInterface $wrapper,
        AbstractReactorSystemData $reactorSystemData
    ) {
        $this->wrapper = $wrapper;
        $this->reactorSystemData = $reactorSystemData;
    }

    public function get(): AbstractReactorSystemData
    {
        return $this->reactorSystemData;
    }

    private function getWarpdriveProduction(): int
    {
        if ($this->warpdriveProduction === null) {
            $warpdrive = $this->wrapper->getWarpDriveSystemData();

            if ($warpdrive === null) {
                $this->warpdriveProduction = 0;
            } else {
                $warpdriveSplit = $warpdrive->getWarpdriveSplit();
                $reactorOutput = $this->getOutputCappedByLoad();
                $flightCost = $this->wrapper->get()->getRump()->getFlightEcost();
                $maxWarpdriveGain = (int)floor(($reactorOutput - $this->wrapper->getEpsUsage()) / $flightCost);

                $this->warpdriveProduction = (int)round((1 - ($warpdriveSplit / 100)) * $maxWarpdriveGain);
            }
        }

        return $this->warpdriveProduction;
    }

    public function getEpsProduction(): int
    {
        if ($this->epsProduction === null) {
            $warpdrive = $this->wrapper->getWarpDriveSystemData();
            $warpdriveSplit = $warpdrive === null ? 100 : $warpdrive->getWarpdriveSplit();

            if ($warpdriveSplit === 0) {
                $this->epsProduction = $this->wrapper->getEpsUsage();
            } else {
                $reactorOutput = $this->getOutputCappedByLoad();
                $warpDriveProduction = $this->getWarpdriveProduction();
                $flightCost = $this->wrapper->get()->getRump()->getFlightEcost();

                $this->epsProduction = $reactorOutput - ($warpDriveProduction * $flightCost);
            }
        }

        return $this->epsProduction;
    }

    public function getEffectiveEpsProduction(): int
    {
        if ($this->effectiveEpsProduction === null) {
            $epsSystem = $this->wrapper->getEpsSystemData();
            $missingEps = $epsSystem === null ? 0 : $epsSystem->getMaxEps() - $epsSystem->getEps();
            $epsGrowthCap = $this->getEpsProduction() - $this->wrapper->getEpsUsage();
            $this->effectiveEpsProduction = min($missingEps, $epsGrowthCap);
        }
        return $this->effectiveEpsProduction;
    }

    public function getEffectiveWarpDriveProduction(): int
    {
        if ($this->effectiveWarpDriveProduction === null) {
            $warpdrive = $this->wrapper->getWarpDriveSystemData();
            $missingWarpdrive = $warpdrive === null ? 0 : $warpdrive->getMaxWarpDrive() - $warpdrive->getWarpDrive();

            $this->effectiveWarpDriveProduction = min($missingWarpdrive, $this->getWarpdriveProduction());
        }

        return $this->effectiveWarpDriveProduction;
    }

    public function getUsage(): int
    {
        return $this->getEffectiveEpsProduction()
            + $this->getEffectiveWarpDriveProduction() * $this->wrapper->get()->getRump()->getFlightEcost();
    }

    public function getCapacity(): int
    {
        return $this->get()->getCapacity();
    }

    public function getOutput(): int
    {
        return $this->get()->getOutput();
    }

    public function setOutput(int $output): ReactorWrapperInterface
    {
        $this->get()->setOutput($output)->update();

        return $this;
    }

    public function getOutputCappedByLoad(): int
    {
        if ($this->getOutput() > $this->getLoad()) {
            return $this->getLoad();
        }

        return $this->getOutput();
    }

    public function getLoad(): int
    {
        return $this->get()->getLoad();
    }

    public function setLoad(int $load): ReactorWrapperInterface
    {
        $this->get()->setLoad($load)->update();

        return $this;
    }

    public function changeLoad(int $amount): ReactorWrapperInterface
    {
        $this->get()->setLoad($this->get()->getLoad() + $amount)->update();

        return $this;
    }

    public function isHealthy(): bool
    {
        return $this->wrapper->get()->isSystemHealthy($this->get()->getSystemType());
    }

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

        return $percentage > 0.3 ? "" :  "color: yellow;";
    }
}
