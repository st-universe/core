<?php

namespace Stu\Module\Maintenance;

use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

final class OldFlightSignatureDeletion implements MaintenanceHandlerInterface
{
    //two days
    public const SIGNATURE_MAX_AGE = 172800;

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    public function __construct(
        FlightSignatureRepositoryInterface $flightSignatureRepository
    ) {
        $this->flightSignatureRepository = $flightSignatureRepository;
    }

    public function handle(): void
    {
        $this->flightSignatureRepository->deleteOldSignatures(OldFlightSignatureDeletion::SIGNATURE_MAX_AGE);
    }
}
