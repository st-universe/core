<?php

declare(strict_types=1);

namespace Stu\Lib\Information;

interface InformationInterface
{
    public function addInformation(?string $information): InformationInterface;

    /** @param string|int|float $args */
    public function addInformationf(string $information, ...$args): InformationInterface;
}
