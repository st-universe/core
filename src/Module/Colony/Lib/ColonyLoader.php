<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Colony;

/**
 * A temporary loader unless the ColonyRepository has arrived
 */
final class ColonyLoader implements ColonyLoaderInterface
{

    public function byId(int $colonyId): Colony
    {
        return new Colony($colonyId);
    }

    public function byIdAndUser(int $colonyId, int $userId): Colony
    {
        $colony = $this->byId($colonyId);
        if ((int) $colony->getUserId() !== $userId) {
            throw new \AccessViolation();
        }
        return $colony;
    }
}