<?php

namespace Stu\Module\Control;

use Noodlehaus\ConfigInterface;

final class StuHash implements StuHashInterface
{
    private ConfigInterface $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    public function hash(string $data): string
    {
        return hash($this->config->get('game.hash_method'), $data);
    }
}
