<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Component\Ship\Wormhole\WormholeEntryModeEnum;
use Stu\Component\Ship\Wormhole\WormholeEntryTypeEnum;
use Stu\Orm\Entity\WormholeRestriction;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class WormholeRestrictionItem
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AllianceRepositoryInterface $allianceRepository,
        private FactionRepositoryInterface $factionRepository,
        private WormholeRestriction $restriction
    ) {}

    public function getId(): int
    {
        return $this->restriction->getId();
    }

    public function getTargetName(): string
    {
        $type = $this->restriction->getPrivilegeType();
        if ($type === null) {
            return 'Unbekannt';
        }

        return match ($type) {
            WormholeEntryTypeEnum::USER => $this->getUserName(),
            WormholeEntryTypeEnum::ALLIANCE => $this->getAllianceName(),
            WormholeEntryTypeEnum::FACTION => $this->getFactionName(),
            WormholeEntryTypeEnum::SHIP => 'Schiff ' . $this->restriction->getTargetId()
        };
    }

    public function getTypeName(): string
    {
        $type = $this->restriction->getPrivilegeType();
        if ($type === null) {
            return 'Unbekannt';
        }

        return match ($type) {
            WormholeEntryTypeEnum::USER => 'Spieler',
            WormholeEntryTypeEnum::ALLIANCE => 'Allianz',
            WormholeEntryTypeEnum::FACTION => 'Rasse',
            WormholeEntryTypeEnum::SHIP => 'Schiff'
        };
    }

    private function getUserName(): string
    {
        $user = $this->userRepository->find($this->restriction->getTargetId());
        return $user === null
            ? 'Unbekannter Spieler'
            : $user->getName();
    }

    private function getAllianceName(): string
    {
        $ally = $this->allianceRepository->find($this->restriction->getTargetId());
        return $ally === null
            ? 'Unbekannte Allianz'
            : $ally->getName();
    }

    private function getFactionName(): string
    {
        $faction = $this->factionRepository->find($this->restriction->getTargetId());
        return $faction === null
            ? 'Unbekannte Rasse'
            : $faction->getName();
    }

    public function getPrivilegeModeString(): string
    {
        $mode = $this->restriction->getMode();
        return $mode === WormholeEntryModeEnum::ALLOW->value ? 'Erlauben' : 'Verbieten';
    }

    public function isAllowed(): bool
    {
        return $this->restriction->getMode() === WormholeEntryModeEnum::ALLOW->value;
    }

    public function getEntry(): object
    {
        return $this->restriction->getWormholeEntry();
    }
}
