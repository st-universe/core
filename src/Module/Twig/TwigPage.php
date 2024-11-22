<?php

declare(strict_types=1);

namespace Stu\Module\Twig;

use Override;
use RuntimeException;
use Twig\Environment;
use Twig\TemplateWrapper;

final class TwigPage implements TwigPageInterface
{
    private ?string $template = null;

    /** @var array<mixed> */
    private array $variables = [];

    public function __construct(private Environment $environment) {}

    #[Override]
    public function setVar(string $var, mixed $value, bool $isGlobal = false): void
    {
        if ($isGlobal) {
            $this->environment->addGlobal($var, $value);
        } else {
            $this->variables[$var] = $value;
        }
    }

    #[Override]
    public function setTemplate(string $file): void
    {
        $this->template = $file;
    }

    #[Override]
    public function isTemplateSet(): bool
    {
        return $this->template !== null;
    }

    #[Override]
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

    public function resetVariables(): void
    {
        $this->template = null;
        $this->variables = [];
    }
}
