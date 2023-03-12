<?php

namespace Stu\Module\Tick\Colony;

interface ColonyTickManagerInterface
{
    public function work(int $batchGroup, int $batchGroupCount): void;
}
