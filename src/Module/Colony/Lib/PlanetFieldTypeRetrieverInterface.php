<?php

namespace Stu\Module\Colony\Lib;

interface PlanetFieldTypeRetrieverInterface
{
    public function getDescription(int $fieldTypeId): string;

    public function getCategory(int $fieldTypeId): int;
}
