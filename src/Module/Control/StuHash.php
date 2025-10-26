<?php

namespace Stu\Module\Control;

use Noodlehaus\ConfigInterface;

final class StuHash implements StuHashInterface
{
    public function __construct(private readonly ConfigInterface $config) {}

    #[\Override]
    public function hash(string $data): string
    {
        return hash($this->config->get('game.hash_method'), $data);
    }
}
