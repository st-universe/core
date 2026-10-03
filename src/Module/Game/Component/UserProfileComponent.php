<?php

declare(strict_types=1);

namespace Stu\Module\Game\Component;

use Stu\Lib\Component\ComponentInterface;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;

/**
 * Renders the user box in the header
 */
final class UserProfileComponent implements ComponentInterface
{
    private const int NEW_USER_DURATION = 7 * 24 * 60 * 60;

    #[\Override]
    public function setTemplateVariables(User $user, TemplateInterface $template): void
    {
        $template->setTemplateVar('PRESTIGE', $user->getPrestige());
        $template->setTemplateVar(
            'IS_NEW_USER',
            $user->getRegistration()->getCreationDate() > time() - self::NEW_USER_DURATION
        );
    }
}
