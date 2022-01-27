<?php

namespace Stu\Module\Tal;

interface TalPageInterface
{
    public function setVar(string $var, $value): void;

    public function setTemplate(string $file): void;

    public function isTemplateSet(): bool;

    public function parse(): string;
}
