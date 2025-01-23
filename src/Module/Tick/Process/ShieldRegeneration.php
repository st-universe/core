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
        $result = $this->spacecraftRepository->getSuitableForShieldRegeneration($time - self::SHIELD_REGENERATION_TIME);
        $processedCount = 0;
        foreach ($result as $spacecraft) {
            $processedCount++;

            $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);
            $rate = $wrapper->getShieldRegenerationRate();
            if ($spacecraft->getShield() + $rate > $spacecraft->getMaxShield()) {
                $rate = $spacecraft->getMaxShield() - $spacecraft->getShield();
            }
            $spacecraft->setShield($spacecraft->getShield() + $rate);

            $shieldSystemData = $wrapper->getShieldSystemData();
            if ($shieldSystemData === null) {
                throw new RuntimeException('this should hot happen');
            }
            $shieldSystemData->setShieldRegenerationTimer($time)->update();

            $this->spacecraftRepository->save($spacecraft);
        }
    }
}
