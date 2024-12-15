<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Override;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class ShieldRegeneration implements ProcessTickHandlerInterface
{
    private const int SHIELD_REGENERATION_TIME = 900;

    public function __construct(private SpacecraftRepositoryInterface $spacecraftRepository) {}

    #[Override]
    public function work(): void
    {
        $time = time();
        $result = $this->spacecraftRepository->getSuitableForShieldRegeneration($time - self::SHIELD_REGENERATION_TIME);
        $processedCount = 0;
        foreach ($result as $obj) {
            $processedCount++;

            $rate = $obj->getShieldRegenerationRate();
            if ($obj->getShield() + $rate > $obj->getMaxShield()) {
                $rate = $obj->getMaxShield() - $obj->getShield();
            }
            $obj->setShield($obj->getShield() + $rate);
            $obj->setShieldRegenerationTimer($time);

            $this->spacecraftRepository->save($obj);
        }

        //$this->loggerUtil->init('shield', LoggerEnum::LEVEL_ERROR);
        //$this->loggerUtil->log(sprintf('shieldRegenDuration:%d s, processedCount: %d', time() - $time, $processedCount));
    }
}
