<?php

declare(strict_types=1);

namespace Stu\Lib;

use Noodlehaus\ConfigInterface;

class Generators
{

    static function generate(ConfigInterface $config)
    {
        $path = sprintf(
            '%s/src/admin/generators/',
            $config->get('game.webroot')
        );

        $files = dir($path);
        while (false !== ($entry = $files->read())) {
            if (!is_file($path . $entry)) {
                continue;
            }
            include_once($path . $entry);
        }
    }

}
