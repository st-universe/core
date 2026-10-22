<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Stu\Component\Realtime\SpacecraftMovementPublisherInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class TachyonScannerShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    //in seconds
    public const int DECLOAK_INTERVAL = 300;

    public const int SCAN_EPS_COST = 10;

    public function __construct(private SpacecraftMovementPublisherInterface $spacecraftMovementPublisher) {}

    #[\Override]
    public function activate(SpacecraftWrapperInterface $wrapper, SpacecraftSystemManagerInterface $manager): void
    {
        parent::activate($wrapper, $manager);
        $this->spacecraftMovementPublisher->publishCoverageChanged($wrapper->get());
    }

    #[\Override]
    public function deactivate(SpacecraftWrapperInterface $wrapper): void
    {
        parent::deactivate($wrapper);
        $this->spacecraftMovementPublisher->publishCoverageChanged($wrapper->get());
    }

    #[\Override]
    public function handleDestruction(SpacecraftWrapperInterface $wrapper): void
    {
        $this->spacecraftMovementPublisher->publishCoverageChanged($wrapper->get());
    }

    #[\Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::TACHYON_SCANNER;
    }

    #[\Override]
    public function getEnergyConsumption(): int
    {
        return 3;
    }
}
