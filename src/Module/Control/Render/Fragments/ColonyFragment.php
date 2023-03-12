<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Stu\Component\Game\GameEnum;
use Stu\Module\Tal\TalPageInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * Renders the colony list in the header
 */
final class ColonyFragment implements RenderFragmentInterface
{
    public function render(
        UserInterface $user,
        TalPageInterface $talPage
    ): void {
        $talPage->setVar(
            'COLONIES',
            $user->getId() === GameEnum::USER_NOONE ? [] : $user->getColonies()
        );
    }
}