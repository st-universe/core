<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Wormhole;

use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\WormholeEntry;
use Stu\Orm\Entity\WormholeRestriction;

final class WormholeEntryPrivilegeUtility implements WormholeEntryPrivilegeUtilityInterface
{
    #[\Override]
    public function checkPrivilegeFor(WormholeEntry $wormholeEntry, User|Spacecraft $source): bool
    {
        try {
            return $wormholeEntry->getRestrictions()->reduce(
                fn (bool $isAllowed, WormholeRestriction $restriction): bool => $isAllowed || $this->isAllowed($restriction, $source),
                false
            );
        } catch (WormholeEntryUnallowedException) {
            return false;
        }
    }

    private function isAllowed(WormholeRestriction $restriction, User|Spacecraft $source): bool
    {
        $user = $source instanceof User ? $source : $source->getUser();
        $userAlliance = $user->getAlliance();

        $privilegeType = $restriction->getPrivilegeType();
        if ($privilegeType === null) {
            return false;
        }

        $isMatch = match ($privilegeType) {
            WormholeEntryTypeEnum::USER => $restriction->getTargetId() === $user->getId(),
            WormholeEntryTypeEnum::ALLIANCE => $userAlliance !== null && $restriction->getTargetId() === $userAlliance->getId(),
            WormholeEntryTypeEnum::FACTION => $restriction->getTargetId() == $user->getFactionId(),
            WormholeEntryTypeEnum::SHIP => $source instanceof Spacecraft && $restriction->getTargetId() == $source->getId(),
        };

        if (!$isMatch) {
            return false;
        }

        if ($restriction->getMode() === WormholeEntryModeEnum::DENY->value) {
            throw new WormholeEntryUnallowedException();
        }

        return true;
    }
}
