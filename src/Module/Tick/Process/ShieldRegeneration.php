<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Component\Ship\ShipEnum;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShieldRegeneration implements ProcessTickInterface
{
    private $shipRepository;

    public function __construct(
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipRepository = $shipRepository;
    }

    public function work(): void
    {
        $time = time();
        $result = $this->shipRepository->getSuitableForShildRegeneration($time - ShipEnum::SHIELD_REGENERATION_TIME);
        foreach ($result as $key => $obj) {
            if ($obj->getCrewCount() < $obj->getBuildplan()->getCrew()) {
                return;
            }
            $rate = $obj->getShieldRegenerationRate();
            if ($obj->getShield() + $rate > $obj->getMaxShield()) {
                $rate = $obj->getMaxShield() - $obj->getShield();
            }
            $obj->setShield($obj->getShield() + $rate);
            $obj->setShieldRegenerationTimer($time);

            $this->shipRepository->save($obj);
        }
    }
}
