<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Orm\Entity\WormholeRestriction;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class SpacecraftUiFactory implements SpacecraftUiFactoryInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private AllianceRepositoryInterface $allianceRepository,
        private FactionRepositoryInterface $factionRepository
    ) {}

    #[\Override]
    public function createWormholeRestrictionItem(
        WormholeRestriction $restriction
    ): WormholeRestrictionItem {
        return new WormholeRestrictionItem(
            $this->userRepository,
            $this->allianceRepository,
            $this->factionRepository,
            $restriction
        );
    }
}
