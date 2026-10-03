<?php

declare(strict_types=1);

namespace Stu\Module\Game\Component;

use Stu\Lib\Component\ComponentInterface;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;

final class NagusComponent implements ComponentInterface
{
    #[\Override]
    public function setTemplateVariables(User $user, TemplateInterface $template): void
    {
        $template->setTemplateVar('SHOW_DEALS', $user->getDeals());
    }
}
