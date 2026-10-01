<?php

declare(strict_types=1);

namespace Stu\Extension;

use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;

interface InteractionGuardInterface
{
    public function getRefusalReason(EntityWithInteractionCheckInterface $source, EntityWithInteractionCheckInterface $target): ?string;
}
