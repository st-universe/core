<?php

declare(strict_types=1);

namespace Stu\Lib\Component;

use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;

/**
 * @template T
 */
interface EntityComponentInterface
{
    /** @param T $entity */
    public function setTemplateVariables(
        $entity,
        TemplateInterface $template,
        User $user): void;
}
