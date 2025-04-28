<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Damage;

use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;

interface SystemDamageInterface
{
    public function checkForDamagedShipSystems(
        SpacecraftWrapperInterface $wrapper,
        DamageWrapper $damageWrapper,
        int $huelleVorher,
        InformationInterface $informations
    ): bool;

    public function destroyRandomShipSystem(
        SpacecraftWrapperInterface $wrapper,
        DamageWrapper $damageWrapper
    ): ?string;

    public function damageRandomShipSystem(
        SpacecraftWrapperInterface $wrapper,
        DamageWrapper $damageWrapper,
        InformationInterface $informations,
        ?int $percent = null
    ): void;

    public function damageShipSystem(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftSystemInterface $system,
        int $dmg,
        InformationInterface $informations
    ): bool;
}
