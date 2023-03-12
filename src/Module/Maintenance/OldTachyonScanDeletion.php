<?php

namespace Stu\Module\Maintenance;

use Stu\Component\Game\TimeConstants;
use Stu\Orm\Repository\TachyonScanRepositoryInterface;

final class OldTachyonScanDeletion implements MaintenanceHandlerInterface
{
    public const SCAN_MAX_AGE = TimeConstants::TWO_DAYS_IN_SECONDS;

    private TachyonScanRepositoryInterface $tachyonScanRepository;

    public function __construct(
        TachyonScanRepositoryInterface $tachyonScanRepository
    ) {
        $this->tachyonScanRepository = $tachyonScanRepository;
    }

    public function handle(): void
    {
        $this->tachyonScanRepository->deleteOldScans(OldTachyonScanDeletion::SCAN_MAX_AGE);
    }
}
