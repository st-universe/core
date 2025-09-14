<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

class SubspaceSystemData extends AbstractSystemData
{
    public ?int $spacecraftId = null;
    public ?int $analyzeTime = null;
    public ?int $flightSigId = null;

    public function __construct(
        SpacecraftSystemRepositoryInterface $shipSystemRepository,
        StatusBarFactoryInterface $statusBarFactory,
        private readonly FlightSignatureRepositoryInterface $flightSignatureRepository
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

    public function getFlightSigId(): ?int
    {
        return $this->flightSigId ?? null;
    }

    public function setFlightSigId(?int $flightSigId): SubspaceSystemData
    {
        $this->flightSigId = $flightSigId;
        return $this;
    }

    public function getHighlightedFlightSig(int $currentTime): ?FlightSignature
    {
        $isSubspaceScannerActive = $this->spacecraft->getSystemState(SpacecraftSystemTypeEnum::SUBSPACE_SCANNER);
        $isMatrixScannerHealthy = $this->spacecraft->isSystemHealthy(SpacecraftSystemTypeEnum::MATRIX_SCANNER);
        $subspaceSystem = $this->spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::SUBSPACE_SCANNER);

        if (
            !$isSubspaceScannerActive
            || !$isMatrixScannerHealthy
            || $subspaceSystem->getData() === null
        ) {
            return null;
        }

        $analyzeTime = $this->getAnalyzeTime();
        if ($analyzeTime === null) {
            return null;
        }

        $minTime = $analyzeTime + (3 * 60);
        $maxTime = $analyzeTime + (10 * 60);

        if ($currentTime < $minTime || $currentTime > $maxTime) {
            return null;
        }

        $flightSigId = $this->getFlightSigId();

        return $flightSigId !== null ? $this->flightSignatureRepository->find($flightSigId) : null;
    }
}
