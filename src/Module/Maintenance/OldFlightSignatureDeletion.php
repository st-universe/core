<?php

namespace Stu\Module\Maintenance;

use Override;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

final class OldFlightSignatureDeletion implements MaintenanceHandlerInterface
{
    public function __construct(private FlightSignatureRepositoryInterface $flightSignatureRepository)
    {
    }

    #[Override]
    public function handle(): void
    {
        $this->flightSignatureRepository->deleteOldSignatures(FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED);
    }
}
