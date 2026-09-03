<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CancelContract;

interface CancelContractRequestInterface
{
    public function getRelationId(): int;
}
