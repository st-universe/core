<?php

namespace Stu\Module\Control;

use Noodlehaus\ConfigInterface;
use Override;

final class StuHash implements StuHashInterface
{
    public function __construct(private ConfigInterface $config)
    {
    }

    #[Override]
    public function hash(string $data): string
    {
        return hash($this->config->get('game.hash_method'), $data);
    }
}
