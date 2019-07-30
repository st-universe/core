<?php

use PhpTal\Php\TalesInternal;
use PhpTal\TalesRegistry;

class TalPage {
	
	private $template = NULL;
	private $tpl_file = NULL;

	function __construct($tpl) {
		$this->tpl_file = $tpl;
	}

	function setVar($var,$value) {
		$this->getTemplate()->set($var,$value);
	}

	public function setRef($var,&$value) {
		$this->getTemplate()->set($var,$value);
	}

	function getTemplate() {
		if ($this->template === NULL) {
			$tr = new PhpTal\GetTextTranslator();

			// TBD: set language
			$tr->setLanguage('de_DE');

			$tr->addDomain('stu', APP_PATH.'/lang');
			$tr->useDomain('stu');

			$this->template = new PhpTal\PHPTAL($this->tpl_file);
			$this->template->setForceReparse(true);
			$this->template->setTranslator($tr);
			$this->template->allowPhpModifier();
			$this->template->setOutputMode(PhpTal\PHPTAL::XHTML);
		}
		return $this->template;
	}

	function setTemplate($file) {
		$this->getTemplate()->setTemplate($file);
	}

	function parse($returnResult=FALSE) {
		$output = &$this->parseXSLT();
		if ($returnResult) {
			return $output;
		}
		ob_start();
		echo $output;
		ob_flush();
	}	

	private function parseXSLT() {
		$xslDom = new DomDocument();
		$xslDom->load(APP_PATH.'src/html/xslt/default.xslt');

		# XML-Daten laden
		$xmlDom = new DomDocument; 
		$file = str_replace('&','&amp;',$this->getTemplate()->execute());
		$xmlDom->loadXML($file);
		 
		$xsl = new XsltProcessor; // XSLT Prozessor Objekt erzeugen 
		$xsl->importStylesheet($xslDom); // Stylesheet laden 
		$data = html_entity_decode($xsl->transformToXML($xmlDom));
		$data = preg_replace('/<(textarea|script)([^>]*)\/>/U','<\\1\\2></\\1>',$data);
		return str_replace("&gt;",">",$data);
	}
}
?>
