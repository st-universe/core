<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\SetKnMark;

interface SetKnMarkRequestInterface
{
    public function getKnOffset(): int;
}
