<?php

declare(strict_types=1);

namespace Stu\Module\Tal;

use DomDocument;
use PhpTal;
use PhpTal\PhpTalInterface;
use XsltProcessor;

final class TalPage implements TalPageInterface
{

    private $template;

    public function setVar(string $var, $value): void
    {
        $this->getTemplate()->set($var, $value);
    }

    private function getTemplate(): PhpTalInterface
    {
        if ($this->template === null) {
            $tr = new PhpTal\GetTextTranslator();

            // TBD: set language
            $tr->setLanguage('de_DE');

            $tr->addDomain('stu', APP_PATH . '/lang');
            $tr->useDomain('stu');

            $this->template = new PhpTal\PHPTAL();
            $this->template->setForceReparse(true);
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
        $xslDom->load(APP_PATH . 'src/html/xslt/default.xslt');

        $xmlDom = new DomDocument();
        $file = str_replace('&', '&amp;', $this->getTemplate()->execute());
        $xmlDom->loadXML($file);

        $xsl = new XsltProcessor; // XSLT Prozessor Objekt erzeugen
        $xsl->importStylesheet($xslDom); // Stylesheet laden
        $data = html_entity_decode($xsl->transformToXML($xmlDom));
        $data = preg_replace('/<(textarea|script)([^>]*)\/>/U', '<\\1\\2></\\1>', $data);
        return str_replace("&gt;", ">", $data);
    }
}
