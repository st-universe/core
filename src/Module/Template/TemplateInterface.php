<?php

declare(strict_types=1);

namespace Stu\Module\Template;

interface TemplateInterface {

    public function setTemplateVar(string $key, mixed $variable): void;
}
