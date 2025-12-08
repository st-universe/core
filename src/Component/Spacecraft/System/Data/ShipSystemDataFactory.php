<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Stu\Component\Spacecraft\System\Exception\InvalidSystemException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Module\Control\GameController;

final class ShipSystemDataFactory implements ShipSystemDataFactoryInterface
{
    public function __construct(
        private readonly ShipRepositoryInterface $shipRepository,
        private readonly SpacecraftSystemRepositoryInterface $shipSystemRepository,
        private readonly TholianWebRepositoryInterface $tholianWebRepository,
        private readonly StatusBarFactoryInterface $statusBarFactory,
        private readonly StuTime $stuTime,
        private readonly DatabaseUserRepositoryInterface $databaseUserRepository,
        private readonly SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private readonly FlightSignatureRepositoryInterface $flightSignatureRepository,
        private readonly GameController $gameController
    ) {}

    #[\Override]
    public function createSystemData(
        SpacecraftSystemTypeEnum $systemType,
        SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ): AbstractSystemData {

        return match ($systemType) {
            SpacecraftSystemTypeEnum::HULL =>  new HullSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::SHIELDS =>  new ShieldSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::EPS =>  new EpsSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::COMPUTER =>  new ComputerSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::TRACKER =>  new TrackerSystemData($this->shipRepository, $spacecraftWrapperFactory, $this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::THOLIAN_WEB =>  new WebEmitterSystemData($this->shipSystemRepository, $this->tholianWebRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::WARPDRIVE =>  new WarpDriveSystemData($this->shipSystemRepository, $this->statusBarFactory, $this->stuTime, $this->spacecraftRumpRepository, $this->databaseUserRepository, $this->gameController),
            SpacecraftSystemTypeEnum::WARPCORE =>  new WarpCoreSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::SINGULARITY_REACTOR =>  new SingularityCoreSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::FUSION_REACTOR =>  new FusionCoreSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::ASTRO_LABORATORY =>  new AstroLaboratorySystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::PHASER =>  new EnergyWeaponSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::TORPEDO =>  new ProjectileLauncherSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR =>  new BussardCollectorSystemData($this->shipSystemRepository, $this->statusBarFactory),
                        SpacecraftSystemTypeEnum::AGGREGATION_SYSTEM =>  new AggregationSystemSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::LSS =>  new LssSystemData($this->shipSystemRepository, $this->statusBarFactory),
            SpacecraftSystemTypeEnum::SUBSPACE_SCANNER => new SubspaceSystemData($this->shipSystemRepository, $this->statusBarFactory, $this->flightSignatureRepository),
            SpacecraftSystemTypeEnum::WARPCORE_CHARGE_TRANSFER => new WarpcoreChargeTransferSystemData($this->shipSystemRepository, $this->statusBarFactory),

            default => throw new InvalidSystemException(sprintf('no system data present for systemType: %d', $systemType->value))
        };
    }
}