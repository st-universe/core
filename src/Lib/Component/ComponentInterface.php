<?php

declare(strict_types=1);

namespace Stu\Lib\Component;

use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;

interface ComponentInterface
{
    public function setTemplateVariables(User $user, TemplateInterface $template): void;
}
