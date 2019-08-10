<?php

abstract class DefaultGenerator {

	private $filepointer = NULL;

	function __construct() {
		$this->deleteFile();
		$this->generateFile();
		$this->writePrefix();
		$this->handle();
	}

	function __destruct() {
		fclose($this->getFilePointer());
	}

	private function deleteFile() {
		unlink(GENERATED_DIR.$this->file);
	}

	private function generateFile() {
		$this->filepointer = fopen(GENERATED_DIR.$this->file,"a+");
	}

	private function writePrefix() {
		$this->write("<?php");
	}

	protected function write($value) {
		fwrite($this->getFilePointer(),$value."\n");
	}

	protected function writeSuffix() {
		$this->write("?>");
	}

	protected function getFilePointer() {
		return $this->filepointer;
	}

	abstract protected function handle();

}

class Generators {

	static function generate() {
		$files = dir(GENERATOR_DIR);
		while (FALSE !== ($entry = $files->read())) {
			if (!is_file(GENERATOR_DIR.$entry)) {
				continue;
			}
			include_once(GENERATOR_DIR.$entry);
		}
	}

}
?>
