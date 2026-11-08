<?php

declare(strict_types=1);

namespace Stu\Component\Research;

enum TechnologyRecipeKindEnum: string
{
    case NANITE_RESEARCH = 'nanite_research';
    case NANITE_PRODUCTION = 'nanite_production';
    case DATASET_COPY = 'dataset_copy';
    case MODULE_OPTIMIZATION = 'module_optimization';

    public function isResearch(): bool
    {
        return $this === self::NANITE_RESEARCH || $this === self::MODULE_OPTIMIZATION;
    }
}
