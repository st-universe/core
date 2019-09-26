<?php

namespace Stu\Lib;

use Noodlehaus\ConfigInterface;

abstract class DefaultGenerator
{

    private $filepointer = null;

    function __construct()
    {
        $this->deleteFile();
        $this->generateFile();
        $this->writePrefix();
        $this->handle();
    }

    function __destruct()
    {
        fclose($this->getFilePointer());
    }

    private function deleteFile()
    {
        // @todo
        global $container;

        @unlink(
            sprintf(
                '%s/src/inc/generated/',
                $container->get(ConfigInterface::class)->get('game.webroot')
            )
        );
    }

    private function generateFile()
    {
        // @todo
        global $container;

        $path = sprintf(
            '%s/src/inc/generated/',
            $container->get(ConfigInterface::class)->get('game.webroot')
        );
        $this->filepointer = fopen($path, "a+");
    }

    private function writePrefix()
    {
        $this->write("<?php");
    }

    protected function write($value)
    {
        fwrite($this->getFilePointer(), $value . "\n");
    }

    protected function writeSuffix()
    {
        $this->write("?>");
    }

    protected function getFilePointer()
    {
        return $this->filepointer;
    }

    abstract protected function handle();

}

?>
