<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Override;
use RuntimeException;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class ShieldRegeneration implements ProcessTickHandlerInterface
{
    private const int SHIELD_REGENERATION_TIME = 900;

    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[Override]
    public function work(): void
    {
        $time = time();
        $regenerationThreshold = $time - self::SHIELD_REGENERATION_TIME;
        $processedCount = 0;
        foreach ($this->spacecraftRepository->getSuitableForShieldRegeneration() as $spacecraft) {

            //AND CAST(ss.data::jsonb->>\'shieldRegenerationTimer\' AS INTEGER) <= :regenerationThreshold
            $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);
            $shieldSystemData = $wrapper->getShieldSystemData();
            if ($shieldSystemData === null) {
                throw new RuntimeException('this should hot happen');
            }

            if ($shieldSystemData->shieldRegenerationTimer > $regenerationThreshold) {
                continue;
            }

            $processedCount++;
            $rate = $wrapper->getShieldRegenerationRate();
            if ($spacecraft->getShield() + $rate > $spacecraft->getMaxShield()) {
                $rate = $spacecraft->getMaxShield() - $spacecraft->getShield();
            }
            $spacecraft->setShield($spacecraft->getShield() + $rate);

            $shieldSystemData->setShieldRegenerationTimer($time)->update();

            $this->spacecraftRepository->save($spacecraft);
        }
    }
}
