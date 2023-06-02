<?php

declare(strict_types=1);

namespace Stu\Module\Twig;

use RuntimeException;
use Twig\Environment;
use Twig\TemplateWrapper;

final class TwigPage implements TwigPageInterface
{
    private Environment $environment;

    private ?string $template = null;

    /** @var array<mixed> */
    private array $variables = [];

    public function __construct(
        Environment $environment
    ) {
        $this->environment = $environment;
    }

    public function setVar(string $var, mixed $value, bool $isGlobal = false): void
    {
        if ($isGlobal) {
            $this->environment->addGlobal($var, $value);
        } else {
            $this->variables[$var] = $value;
        }
    }

    public function setTemplate(string $file): void
    {
        $this->template = $file;
    }

    public function isTemplateSet(): bool
    {
        return $this->template !== null;
    }

    public function render(): string
    {
        return $this->loadTemplate()->render($this->variables);
    }

    private function loadTemplate(): TemplateWrapper
    {
        if ($this->template === null) {
            throw new RuntimeException('render not possible if template is not set');
        }

        return  $this->environment->load($this->template);
    }
}
