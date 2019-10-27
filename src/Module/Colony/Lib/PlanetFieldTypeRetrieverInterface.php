<?php

namespace Stu\Module\Colony\Lib;

interface PlanetFieldTypeRetrieverInterface
{
    public function getDescription(int $fieldTypeId): string;
}
