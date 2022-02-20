<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Component\Ship\ShipEnum;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShieldRegeneration implements ProcessTickInterface
{
    private ShipRepositoryInterface $shipRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipRepository = $shipRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function work(): void
    {
        $time = time();
        $result = $this->shipRepository->getSuitableForShildRegeneration($time - ShipEnum::SHIELD_REGENERATION_TIME);
        $skippedCount = 0;
        foreach ($result as $obj) {
            //TODO into query!
            if (!$obj->hasEnoughCrew()) {
                $skippedCount++;
                continue;
            }

            $rate = $obj->getShieldRegenerationRate();
            if ($obj->getShield() + $rate > $obj->getMaxShield()) {
                $rate = $obj->getMaxShield() - $obj->getShield();
            }
            $obj->setShield($obj->getShield() + $rate);
            $obj->setShieldRegenerationTimer($time);

            $this->shipRepository->save($obj);
        }

        $this->loggerUtil->init('shield', LoggerEnum::LEVEL_ERROR);
        $this->loggerUtil->log(sprintf('shieldRegenDuration:%d s, skippedCount: %d', time() - $time, $skippedCount));
    }
}
