<?php

declare(strict_types=1);

namespace Stu\Module\Tal;

use Noodlehaus\ConfigInterface;
use PhpTal;
use PhpTal\PhpTalInterface;

final class TalPage implements TalPageInterface
{
    private ConfigInterface $config;

    private ?PhpTal\PHPTAL $template = null;

    private bool $isTemplateSet = false;

    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    public function setVar(string $var, $value): void
    {
        $this->getTemplate()->set($var, $value);
    }

    private function getTemplate(): PhpTalInterface
    {
        if ($this->template === null) {
            $template_repository = realpath(
                sprintf(
                    '%s/../',
                    $this->config->get('game.webroot')
                )
            );

            $this->template = new PhpTal\PHPTAL();
            $this->template->setTemplateRepository($template_repository);
            $this->template->setPhpCodeDestination(sprintf(
                '%s/stu/%d/tal',
                $this->config->get('game.temp_dir'),
                $this->config->get('game.version')
            ));
            $this->template->setForceReparse((bool) $this->config->get('debug.debug_mode'));
            $this->template->allowPhpModifier();
            $this->template->setOutputMode(PhpTal\PHPTAL::XHTML);
        }
        return $this->template;
    }

    public function setTemplate(string $file): void
    {
        $this->getTemplate()->setTemplate($file);
        $this->isTemplateSet = true;
    }

    public function isTemplateSet(): bool
    {
        return $this->isTemplateSet;
    }

    public function parse(): string
    {
        return $this->getTemplate()->execute();
    }
}
