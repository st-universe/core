<?php

declare(strict_types=1);

namespace Stu\Module\Twig;

use RuntimeException;
use Twig\Environment;
use Twig\TemplateWrapper;

//TODO unit tests
final class TwigPage implements TwigPageInterface
{
    private Environment $environment;

    private ?TemplateWrapper $template = null;

    private bool $isTemplateSet = false;

    /** @var array<mixed> */
    private array $variables = [];

    public function __construct(
        Environment $environment
    ) {
        $this->environment = $environment;
    }

    public function setVar(string $var, mixed $value): void
    {
        $this->variables[$var] = $value;
    }

    public function setTemplate(string $file): void
    {
        $this->loadTemplate($file);
        $this->isTemplateSet = true;
    }

    public function isTemplateSet(): bool
    {
        return $this->isTemplateSet;
    }

    public function render(): string
    {
        if ($this->template === null) {
            throw new RuntimeException('can not render before template loaded');
        }

        return $this->template->render($this->variables);
    }

    private function loadTemplate(string $file): TemplateWrapper
    {
        if ($this->template === null) {

            $this->template = $this->environment->load($file);
        }
        return $this->template;
    }
}
