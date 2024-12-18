<?php

namespace Stu\Lib\Component;

class RegisteredComponent
{
    public function __construct(
        public readonly ComponentEnumInterface $componentEnum,
        public readonly ?object $entity
    ) {}
}
