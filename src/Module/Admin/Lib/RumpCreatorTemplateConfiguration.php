<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Lib;

final readonly class RumpCreatorTemplateConfiguration
{
    public function __construct(
        public RumpCreatorData $baseValues,
        public RumpCreatorData $moduleLevels,
        public RumpCreatorData $models,
        public RumpCreatorData $costs,
        public RumpCreatorData $moduleSpecialIds,
        public RumpCreatorData $buildingFunctionIds,
        public RumpCreatorData $specialAbilityIds,
        public RumpCreatorData $colonizationBuildings
    ) {}
}
