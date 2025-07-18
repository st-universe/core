<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;

class SubspaceSystemData extends AbstractSystemData
{
    public ?int $spacecraftId = null;
    public ?int $analyzeTime = null;

    public function __construct(
        SpacecraftSystemRepositoryInterface $shipSystemRepository,
        StatusBarFactoryInterface $statusBarFactory
    ) {
        parent::__construct($shipSystemRepository, $statusBarFactory);
    }

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::SUBSPACE_SCANNER;
    }

    public function getSpacecraftId(): ?int
    {
        return $this->spacecraftId ?? null;
    }

    public function setSpacecraftId(?int $spacecraftId): SubspaceSystemData
    {
        $this->spacecraftId = $spacecraftId;
        return $this;
    }

    public function getAnalyzeTime(): ?int
    {
        return $this->analyzeTime ?? null;
    }

    public function setAnalyzeTime(?int $analyzeTime): SubspaceSystemData
    {
        $this->analyzeTime = $analyzeTime;
        return $this;
    }

    public function getHighlightedSpacecraftId(Spacecraft $spacecraft): ?int
    {
        $isSubspaceScannerActive = $spacecraft->getSystemState(SpacecraftSystemTypeEnum::SUBSPACE_SCANNER);
        if (!$isSubspaceScannerActive) {
            return null;
        }

        $isMatrixScannerHealthy = $spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::MATRIX_SCANNER);
        if (!$isMatrixScannerHealthy) {
            return null;
        }

        $subspaceSystem = $spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::SUBSPACE_SCANNER);
        if ($subspaceSystem->getData() === null) {
            return null;
        }

        $analyzeTime = $this->getAnalyzeTime();
        if ($analyzeTime === null) {
            return null;
        }

        $currentTime = time();
        $minTime = $analyzeTime + (3 * 60);
        $maxTime = $analyzeTime + (10 * 60);

        if (!($currentTime >= $minTime && $currentTime <= $maxTime)) {
            return null;
        }

        return $this->getSpacecraftId();
    }
}
