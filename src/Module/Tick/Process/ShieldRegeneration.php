<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Ship;
use ShipData;

final class ShieldRegeneration implements ProcessTickInterface
{
    public function work(): void
    {
        $time = strtotime(date("d.m.Y H:i", time()));
        $result = Ship::getObjectsBy(
            'WHERE rumps_id NOT IN (SELECT id FROM stu_rumps WHERE category_id=' . SHIP_CATEGORY_DEBRISFIELD . ') AND schilde<max_schilde AND shield_regeneration_timer<=' . ($time - SHIELD_REGENERATION_TIME)
        );
        foreach ($result as $key => $obj) {
            /**
             * @var ShipData $obj
             */
            $obj->regenerateShields($time);
        }
    }
}