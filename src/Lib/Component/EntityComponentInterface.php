<?php

declare(strict_types=1);

namespace Stu\Lib\Component;

use Stu\Module\Control\GameControllerInterface;

/**
 * @template T
 */
interface EntityComponentInterface
{
    /** @param T $entity */
    public function setTemplateVariables($entity, GameControllerInterface $game): void;
}
