<?php

declare(strict_types=1);

namespace Stu\Component\Map\Effects\Type;

use Override;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\LocationInterface;

class RegenerationChanceEffectHandler implements EffectHandlerInterface
{
    /** @var array<SpacecraftModuleTypeEnum> */
    private const array POSSIBLE_SYSTEM_TYPES = [
        SpacecraftModuleTypeEnum::EPS,
        SpacecraftModuleTypeEnum::REACTOR,
        SpacecraftModuleTypeEnum::SHIELDS,
        SpacecraftModuleTypeEnum::WARPDRIVE
    ];

    public function __construct(private StuRandom $stuRandom) {}

    #[Override]
    public function handleSpacecraftTick(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void
    {
        $this->triggerChance($wrapper, $information);
    }

    #[Override]
    public function addFlightInformation(LocationInterface $location, MessageCollectionInterface $messages): void
    {
        $messages->addInformationf(
            "[color=green]Konstruktives Partikelresonanzfeld durch %s in Sektor %s kann zu einem Energietransfer führen.[/color]",
            $location->getFieldType()->getName(),
            $location->getSectorString()
        );
    }

    #[Override]
    public function handleIncomingSpacecraft(SpacecraftWrapperInterface $wrapper, MessageCollectionInterface $messages): void
    {
        $this->triggerChance($wrapper, $messages);
    }

    private function triggerChance(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void
    {
        if ($this->stuRandom->rand(1, 100) > 20) {
            return;
        }

        $moduleType = self::POSSIBLE_SYSTEM_TYPES[array_rand(self::POSSIBLE_SYSTEM_TYPES)];
        $percentage = $this->stuRandom->rand(1, 20, true, 5);

        $gain = match ($moduleType) {
            SpacecraftModuleTypeEnum::EPS => $this->regenerateEPS($wrapper, $percentage, $information),
            SpacecraftModuleTypeEnum::SHIELDS => $this->regenerateSHIELDS($wrapper, $percentage, $information),
            SpacecraftModuleTypeEnum::REACTOR => $this->regenerateREACTOR($wrapper, $percentage, $information),
            SpacecraftModuleTypeEnum::WARPDRIVE => $this->regenerateWARPDRIVE($wrapper, $percentage, $information)
        };

        if ($gain !== null) {
            $information->addInformationf(
                "%s: [color=green]%s wird um %s aufgeladen[/color]",
                $wrapper->get()->getName(),
                $moduleType->getSystemType()->getDescription(),
                $gain
            );
        }
    }

    private function regenerateEPS(SpacecraftWrapperInterface $wrapper, int $percentage, InformationInterface $information): ?int
    {
        $epsSystemData = $wrapper->getEpsSystemData();
        if ($epsSystemData === null) {
            return null;
        }

        $spacecraft = $wrapper->get();
        if (!$spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::EPS)) {
            return null;
        }

        $gain = min(
            $epsSystemData->getMaxEps() - $epsSystemData->getEps(),
            $this->stuRandom->rand(
                1,
                (int)ceil($epsSystemData->getTheoreticalMaxEps() / 100 * $percentage),
                true,
                (int)ceil($epsSystemData->getTheoreticalMaxEps() / 50 * $percentage),
            )
        );

        $epsSystemData->lowerEps(-$gain)->update();

        return $gain;
    }

    private function regenerateREACTOR(SpacecraftWrapperInterface $wrapper, int $percentage, InformationInterface $information): ?int
    {
        $reactorWrapper = $wrapper->getReactorWrapper();
        if ($reactorWrapper === null) {
            return null;
        }

        if (!$reactorWrapper->isHealthy()) {
            return null;
        }

        $capacity = $reactorWrapper->getCapacity();

        $gain = min(
            $capacity - $reactorWrapper->getLoad(),
            $this->stuRandom->rand(
                1,
                (int)ceil($capacity / 100 * $percentage),
                true,
                (int)ceil($capacity / 50 * $percentage),
            )
        );

        $reactorWrapper->changeLoad($gain);

        return $gain;
    }

    private function regenerateSHIELDS(SpacecraftWrapperInterface $wrapper, int $percentage, InformationInterface $information): ?int
    {
        $spacecraft = $wrapper->get();
        if (!$spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::SHIELDS)) {
            return null;
        }

        $maxShields = $spacecraft->getMaxShield(true);

        $gain = min(
            $maxShields - $spacecraft->getShield(),
            $this->stuRandom->rand(
                1,
                (int)ceil($maxShields / 100 * $percentage),
                true,
                (int)ceil($maxShields / 50 * $percentage),
            )
        );

        $spacecraft->setShield($spacecraft->getShield() + $gain);

        return $gain;
    }

    private function regenerateWARPDRIVE(SpacecraftWrapperInterface $wrapper, int $percentage, InformationInterface $information): ?int
    {
        $warpdriveData = $wrapper->getWarpDriveSystemData();
        if ($warpdriveData === null) {
            return null;
        }

        $spacecraft = $wrapper->get();
        if (!$spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::WARPDRIVE)) {
            return null;
        }

        $maxWarpdrive = $warpdriveData->getTheoreticalMaxWarpdrive();

        $gain = min(
            $maxWarpdrive - $warpdriveData->getWarpDrive(),
            $this->stuRandom->rand(
                1,
                (int)ceil($maxWarpdrive / 100 * $percentage),
                true,
                (int)ceil($maxWarpdrive / 50 * $percentage),
            )
        );

        $warpdriveData->lowerWarpDrive(-$gain)->update();

        return $gain;
    }
}
