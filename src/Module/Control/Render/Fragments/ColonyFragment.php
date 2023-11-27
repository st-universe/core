<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Tal\TalPageInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * Renders the colony list in the header
 */
final class ColonyFragment implements RenderFragmentInterface
{
    public function render(
        UserInterface $user,
        TalPageInterface|TwigPageInterface $page
    ): void {
        $page->setVar(
            'COLONIES',
            $user->getId() === UserEnum::USER_NOONE ? [] : $user->getColonies()->toArray()
        );
    }
}
