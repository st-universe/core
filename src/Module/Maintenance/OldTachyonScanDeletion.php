<?php

namespace Stu\Module\Maintenance;

use Stu\Orm\Repository\TachyonScanRepositoryInterface;

final class OldTachyonScanDeletion implements MaintenanceHandlerInterface
{
    //two days
    public const SCAN_MAX_AGE = 172800;

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
