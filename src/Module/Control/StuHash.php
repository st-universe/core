<?php

namespace Stu\Module\Control;

use Stu\Module\Config\StuConfigInterface;

final class StuHash implements StuHashInterface
{
    public function __construct(private readonly StuConfigInterface $config) {}

    #[\Override]
    public function hash(string $data): string
    {
        return hash($this->config->getGameSettings()->getHashMethod(), $data);
    }
}
