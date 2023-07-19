<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Component\Ship\ShipEnum;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShieldRegeneration implements ProcessTickHandlerInterface
{
    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipRepository = $shipRepository;
    }

    public function work(): void
    {
        $time = time();
        $result = $this->shipRepository->getSuitableForShildRegeneration($time - ShipEnum::SHIELD_REGENERATION_TIME);
        $processedCount = 0;
        foreach ($result as $obj) {
            $processedCount++;

            $rate = $obj->getShieldRegenerationRate();
            if ($obj->getShield() + $rate > $obj->getMaxShield()) {
                $rate = $obj->getMaxShield() - $obj->getShield();
            }
            $obj->setShield($obj->getShield() + $rate);
            $obj->setShieldRegenerationTimer($time);

            $this->shipRepository->save($obj);
        }

        //$this->loggerUtil->init('shield', LoggerEnum::LEVEL_ERROR);
        //$this->loggerUtil->log(sprintf('shieldRegenDuration:%d s, processedCount: %d', time() - $time, $processedCount));
    }
}
