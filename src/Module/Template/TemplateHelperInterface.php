<?php

declare(strict_types=1);

namespace Stu\Module\Template;

use Stu\Orm\Entity\PlanetField;

interface TemplateHelperInterface
{
    public function formatProductionValue(int $value): string;

    public function addPlusCharacter(string $value): string;

    public function jsquote(string $str): string;

    public function formatSeconds(string $time): string;

    public function getNumberWithThousandSeperator(int $number): string;

    public function getPlanetFieldTypeDescription(int $fieldTypeId): string;

    public function getPlanetFieldTitle(PlanetField $planetField): string;
}
