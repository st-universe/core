<?php

namespace Stu\Module\Tick\Colony;

interface ColonyTickManagerInterface
{
    public function work(int $tickId, int $batchGroup, int $batchGroupCount): void;
}
