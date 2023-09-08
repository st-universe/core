<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

class LssCellData
{
    private ?string $systemBackgroundId = null;
    private ?int $fieldGraphicId = null;
    private ?string $mapGraphicPath = null;
    private bool $colonyShieldState = false;
    private ?string $subspaceCode = null;
    private string $displayCount = '';

    public function __construct(
        ?string $systemBackgroundId,
        ?int $fieldGraphicId,
        ?string $mapGraphicPath,
        bool $colonyShieldState,
        ?string $subspaceCode,
        string $displayCount
    ) {
        $this->systemBackgroundId = $systemBackgroundId;
        $this->fieldGraphicId = $fieldGraphicId;
        $this->mapGraphicPath = $mapGraphicPath;
        $this->colonyShieldState = $colonyShieldState;
        $this->subspaceCode = $subspaceCode;
        $this->displayCount = $displayCount;
    }

    public function getSystemBackgroundId(): ?string
    {
        return $this->systemBackgroundId;
    }

    public function getFieldGraphicID(): ?int
    {
        return $this->fieldGraphicId;
    }

    public function getMapGraphicPath(): ?string
    {
        return $this->mapGraphicPath;
    }

    public function getColonyShieldState(): bool
    {
        return $this->colonyShieldState;
    }

    public function getSubspaceCode(): ?string
    {
        return $this->subspaceCode;
    }

    public function getDisplayCount(): string
    {
        return $this->displayCount;
    }
}
