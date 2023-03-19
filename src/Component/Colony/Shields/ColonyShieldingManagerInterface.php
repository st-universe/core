<?php

declare(strict_types=1);

namespace Stu\Component\Colony\Shields;

interface ColonyShieldingManagerInterface
{
    public function updateActualShields(): void;

    /**
     * Returns `true` if the colony has shielding (which is provided by special buildings)
     */
    public function hasShielding(): bool;

    /**
     * Returns the maximum shield load value
     */
    public function getMaxShielding(): int;

    /**
     * Returns `true` if the colony has active shielding (which is provided by special buildings)
     */
    public function isShieldingEnabled(): bool;
}
