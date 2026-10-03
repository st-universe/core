<?php

declare(strict_types=1);

namespace Stu\Module\Template;

use Stu\Module\Twig\TwigPageInterface;

final class Template implements TemplateInterface {

    public function __construct(
        private readonly TwigPageInterface $twigPage
    ) {}

    public function setTemplateVar(string $key, mixed $variable): void {
        $this->twigPage->setVar($key, $variable);
    }
}
