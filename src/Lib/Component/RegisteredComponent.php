<?php

declare(strict_types=1);

namespace Stu\Lib\Component;

class RegisteredComponent
{
    public function __construct(
        public readonly ComponentEnumInterface $componentEnum,
        public readonly ?EntityWithComponentsInterface $entity
    ) {}
}
