<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Data;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

abstract class AbstractReactorSystemData extends AbstractSystemData
{
    public int $output = 0;
    public int $load = 0;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    public function __construct(ShipSystemRepositoryInterface $shipSystemRepository)
    {
        $this->shipSystemRepository = $shipSystemRepository;
    }

    public function update(): void
    {
        $this->updateSystemData(
            $this->getSystemType(),
            $this,
            $this->shipSystemRepository
        );
    }

    abstract function getSystemType(): ShipSystemTypeEnum;

    public abstract function getIcon(): string;

    public abstract function getLoadUnits(): int;

    /** @return array<int, int> */
    public abstract function getLoadCost(): array;

    public abstract function getCapacity(): int;

    /**
     * proportional to reactor system status
     */
    public function getOutput(): int
    {
        return (int) (ceil($this->output
            * $this->ship->getShipSystem($this->getSystemType())->getStatus() / 100));
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
