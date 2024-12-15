<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

abstract class AbstractReactorSystemData extends AbstractSystemData
{
    public int $output = 0;
    public int $load = 0;

    abstract public function getIcon(): string;

    abstract public function getLoadUnits(): int;

    /** @return array<int, int> */
    abstract public function getLoadCost(): array;

    abstract public function getCapacity(): int;

    /**
     * proportional to reactor system status
     */
    public function getOutput(): int
    {
        return (int) (ceil($this->output
            * $this->spacecraft->getShipSystem($this->getSystemType())->getStatus() / 100));
    }

    public function getTheoreticalReactorOutput(): int
    {
        return $this->output;
    }

    public function setOutput(int $output): AbstractReactorSystemData
    {
        $this->output = $output;
        return $this;
    }

    public function getLoad(): int
    {
        return $this->load;
    }

    public function setLoad(int $load): AbstractReactorSystemData
    {
        $this->load = $load;

        return $this;
    }
}
