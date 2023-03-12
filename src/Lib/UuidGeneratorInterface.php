<?php

declare(strict_types=1);

namespace Stu\Lib;


interface UuidGeneratorInterface
{
    /**
     * Generates a uuid v4
     */
    public function genV4(): string;
}