<?php

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\System\Data\AbstractReactorSystemData;
use Stu\Module\Commodity\CommodityTypeEnum;

interface ReactorWrapperInterface
{
    // output to capacity multipliers
    public const FUSIONCORE_CAPACITY_MULTIPLIER = 10;
    public const WARPCORE_CAPACITY_MULTIPLIER = 15;
    public const SINGULARITY_CAPACITY_MULTIPLIER = 20;

    // load commodities
    public const WARPCORE_LOAD = 30;
    public const WARPCORE_LOAD_COST = [
        CommodityTypeEnum::COMMODITY_DILITHIUM => 1,
        CommodityTypeEnum::COMMODITY_ANTIMATTER => 2,
        CommodityTypeEnum::COMMODITY_DEUTERIUM => 2
    ];

    public const SINGULARITY_CORE_LOAD = 85;
    public const SINGULARITY_CORE_LOAD_COST = [
        CommodityTypeEnum::COMMODITY_DILITHIUM => 2,
        CommodityTypeEnum::COMMODITY_ANTIMATTER => 3,
        CommodityTypeEnum::COMMODITY_PLASMA => 2
    ];

    public const FUSION_REACTOR_LOAD = 2;
    public const FUSION_REACTOR_LOAD_COST = [
        CommodityTypeEnum::COMMODITY_DEUTERIUM => 1
    ];

    public function get(): AbstractReactorSystemData;

    public function getEpsProduction(): int;

    public function getEffectiveEpsProduction(): int;

    public function getEffectiveWarpDriveProduction(): int;

    public function getUsage(): int;

    public function getCapacity(): int;

    public function getOutput(): int;

    public function setOutput(int $output): ReactorWrapperInterface;

    public function getOutputCappedByLoad(): int;

    public function getLoad(): int;

    public function setLoad(int $load): ReactorWrapperInterface;

    public function changeLoad(int $amount): ReactorWrapperInterface;

    public function getReactorLoadStyle(): string;

    public function isHealthy(): bool;
}