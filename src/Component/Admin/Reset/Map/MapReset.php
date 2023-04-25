<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset\Map;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

final class MapReset implements MapResetInterface
{
    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    private UserMapRepositoryInterface $userMapRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        FlightSignatureRepositoryInterface $flightSignatureRepository,
        UserMapRepositoryInterface $userMapRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->flightSignatureRepository = $flightSignatureRepository;
        $this->userMapRepository = $userMapRepository;
        $this->entityManager = $entityManager;
    }

    public function deleteAllFlightSignatures(): void
    {
        echo "  - delete all flight signatures\n";

        $this->flightSignatureRepository->truncateAllSignatures();

        $this->entityManager->flush();
    }

    public function deleteAllUserMaps(): void
    {
        echo "  - delete all user maps\n";

        $this->userMapRepository->truncateAllUserMaps();

        $this->entityManager->flush();
    }
}
