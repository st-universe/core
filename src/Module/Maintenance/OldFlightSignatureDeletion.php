<?php

namespace Stu\Module\Maintenance;

use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

final class OldFlightSignatureDeletion implements MaintenanceHandlerInterface
{
    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    public function __construct(
        FlightSignatureRepositoryInterface $flightSignatureRepository
    ) {
        $this->flightSignatureRepository = $flightSignatureRepository;
    }

    public function handle(): void
    {
        $this->flightSignatureRepository->deleteOldSignatures(FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED);
    }
}
