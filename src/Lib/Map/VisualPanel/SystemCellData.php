<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

class SystemCellData extends CellData
{
    public const ONLY_BACKGROUND_IMAGE = 1;

    private int $x;
    private int $y;
    private int $systemFieldId;
    private bool $colonyShieldState;

    public function __construct(
        int $x,
        int $y,
        int $systemFieldId,
        bool $colonyShieldState,
        ?string $subspaceCode,
        ?string $displayCount
    ) {
        parent::__construct($subspaceCode, $displayCount);

        $this->x = $x;
        $this->y = $y;
        $this->systemFieldId = $systemFieldId;
        $this->colonyShieldState = $colonyShieldState;
    }

    public function getSystemBackgroundId(): string
    {
        return sprintf(
            '%02d%02d',
            $this->y,
            $this->x
        );
    }

    public function getSystemFieldId(): ?int
    {
        if ($this->systemFieldId === self::ONLY_BACKGROUND_IMAGE) {
            return null;
        }

        return $this->systemFieldId;
    }

    public function getColonyShieldState(): bool
    {
        return $this->colonyShieldState;
    }
}
