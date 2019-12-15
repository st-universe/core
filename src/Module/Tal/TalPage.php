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
            $tr = new PhpTal\GetTextTranslator();

            $tr->setLanguage($this->config->get('game.language'));

            $tr->addDomain('stu', $this->config->get('game.webroot') . '/lang');
            $tr->useDomain('stu');

            $this->template = new PhpTal\PHPTAL();
            $this->template->setPhpCodeDestination(sprintf(
                '%s/stu/%s/tal',
                $this->config->get('game.temp_dir'),
                $this->config->get('game.version')
            ));
            $this->template->setForceReparse((bool) $this->config->get('debug.debug_mode'));
            $this->template->setTranslator($tr);
            $this->template->allowPhpModifier();
            $this->template->setOutputMode(PhpTal\PHPTAL::XHTML);
        }
        return $this->template;
    }

    public function setTemplate(string $file): void
    {
        $this->getTemplate()->setTemplate(
            sprintf(
                '%s/%s',
                $this->config->get('game.webroot'),
                $file
            )
        );
    }

    public function parse(): string
    {
        return $this->getTemplate()->execute();
    }
}
