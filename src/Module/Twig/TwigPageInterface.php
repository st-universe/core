<?php

namespace Stu\Module\Twig;

interface TwigPageInterface
{
    public function setVar(string $var, mixed $value, bool $isGlobal = false): void;

    public function setTemplate(string $file): void;

    public function isTemplateSet(): bool;

    public function render(): string;

    public function resetVariables(): void;
}
