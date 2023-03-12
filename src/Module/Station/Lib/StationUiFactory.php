<?php

declare(strict_types=1);

namespace Stu\Module\Station\Lib;

use Stu\Orm\Entity\DockingPrivilegeInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

/**
 * Creates ui and station related items
 */
final class StationUiFactory implements StationUiFactoryInterface
{
    private UserRepositoryInterface $userRepository;

    private AllianceRepositoryInterface $allianceRepository;

    private FactionRepositoryInterface $factionRepository;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        AllianceRepositoryInterface $allianceRepository,
        FactionRepositoryInterface $factionRepository,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->userRepository = $userRepository;
        $this->allianceRepository = $allianceRepository;
        $this->factionRepository = $factionRepository;
        $this->shipRepository = $shipRepository;
    }

    public function createDockingPrivilegeItem(
        DockingPrivilegeInterface $dockingPrivilege
    ): DockingPrivilegeItem {
        return new DockingPrivilegeItem(
            $this->userRepository,
            $this->allianceRepository,
            $this->factionRepository,
            $this->shipRepository,
            $dockingPrivilege
        );
    }
}
