<?php

declare(strict_types=1);

namespace Stu\Module\Tal;

use Override;
use PhpTal;
use PhpTal\PhpTalInterface;
use Stu\Module\Config\StuConfigInterface;

final class TalPage implements TalPageInterface
{
    private ?PhpTal\PHPTAL $template = null;

    private bool $isTemplateSet = false;

    public function __construct(private StuConfigInterface $stuConfig)
    {
    }

    #[Override]
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
                    $this->stuConfig->getGameSettings()->getWebroot()
                )
            );

            $this->template = new PhpTal\PHPTAL();
            $this->template->setTemplateRepository($template_repository);
            $this->template->setPhpCodeDestination(sprintf(
                '%s/stu/%s/tal',
                $this->stuConfig->getGameSettings()->getTempDir(),
                $this->stuConfig->getGameSettings()->getVersion()
            ));
            $this->template->setForceReparse($this->stuConfig->getDebugSettings()->isDebugMode());
            $this->template->allowPhpModifier();
            $this->template->setOutputMode(PhpTal\PHPTAL::XHTML);
        }
        return $this->template;
    }

    #[Override]
    public function setTemplate(string $file): void
    {
        $this->getTemplate()->setTemplate($file);
        $this->isTemplateSet = true;
    }

    #[Override]
    public function isTemplateSet(): bool
    {
        return $this->isTemplateSet;
    }

    #[Override]
    public function parse(): string
    {
        return $this->getTemplate()->execute();
    }
}
