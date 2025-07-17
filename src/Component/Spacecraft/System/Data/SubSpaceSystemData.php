<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;

class SubSpaceSystemData extends AbstractSystemData
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

    public function setSpacecraftId(?int $spacecraftId): SubSpaceSystemData
    {
        $this->spacecraftId = $spacecraftId;
        return $this;
    }

    public function getAnalyzeTime(): ?int
    {
        return $this->analyzeTime ?? null;
    }

    public function setAnalyzeTime(?int $analyzeTime): SubSpaceSystemData
    {
        $this->analyzeTime = $analyzeTime;
        return $this;
    }
}
