<?php

namespace Stu\Module\Maintenance;

use Stu\Component\Game\TimeConstants;
use Stu\Orm\Repository\TachyonScanRepositoryInterface;

final class OldTachyonScanDeletion implements MaintenanceHandlerInterface
{
    public const int SCAN_MAX_AGE = TimeConstants::TWO_DAYS_IN_SECONDS;

    public function __construct(private TachyonScanRepositoryInterface $tachyonScanRepository) {}

    #[\Override]
    public function handle(): void
    {
        $this->tachyonScanRepository->deleteOldScans(OldTachyonScanDeletion::SCAN_MAX_AGE);
    }
}
