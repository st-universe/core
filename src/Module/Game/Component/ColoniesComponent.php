<?php

declare(strict_types=1);

namespace Stu\Module\Game\Component;

use Stu\Lib\Component\ComponentInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;

/**
 * Renders the colony list in the header
 */
final class ColoniesComponent implements ComponentInterface
{
    #[\Override]
    public function setTemplateVariables(User $user, TemplateInterface $template): void
    {
        $template->setTemplateVar(
            'USER_COLONIES',
            ($user->getId() === UserConstants::USER_NOONE) ? [] : $user->getColonies()
        );
    }
}
