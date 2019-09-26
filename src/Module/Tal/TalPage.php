<?php

declare(strict_types=1);

namespace Stu\Module\Tal;

use DomDocument;
use Noodlehaus\ConfigInterface;
use PhpTal;
use PhpTal\PhpTalInterface;
use XsltProcessor;

final class TalPage implements TalPageInterface
{
    private $config;

    private $template;

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
            $this->template->setForceReparse((bool) $this->config->get('debug.debug_mode'));
            $this->template->setTranslator($tr);
            $this->template->allowPhpModifier();
            $this->template->setOutputMode(PhpTal\PHPTAL::XHTML);
        }
        return $this->template;
    }

    public function setTemplate(string $file): void
    {
        $this->getTemplate()->setTemplate($file);
    }

    public function parse(bool $returnResult = false)
    {
        $output = $this->parseXslt();
        if ($returnResult) {
            return $output;
        }
        ob_start();
        echo $output;
        ob_flush();
    }

    private function parseXslt(): string
    {
        $xslDom = new DomDocument();
        $xslDom->load($this->config->get('game.webroot') . '/html/xslt/default.xslt');

        $xmlDom = new DomDocument();
        $file = str_replace('&', '&amp;', $this->getTemplate()->execute());
        $xmlDom->loadXML($file);

        $xsl = new XsltProcessor();
        $xsl->importStylesheet($xslDom);
        $data = html_entity_decode($xsl->transformToXML($xmlDom));
        $data = preg_replace('/<(textarea|script)([^>]*)\/>/U', '<\\1\\2></\\1>', $data);
        return str_replace("&gt;", ">", $data);
    }
}
