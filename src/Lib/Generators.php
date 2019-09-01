<?php

declare(strict_types=1);

namespace Stu\Lib;

class Generators
{

    static function generate()
    {
        $files = dir(GENERATOR_DIR);
        while (false !== ($entry = $files->read())) {
            if (!is_file(GENERATOR_DIR . $entry)) {
                continue;
            }
            include_once(GENERATOR_DIR . $entry);
        }
    }

}