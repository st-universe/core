<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * Renders the colony list in the header
 */
final class ColonyFragment implements RenderFragmentInterface
{
    #[Override]
    public function render(
        UserInterface $user,
        TwigPageInterface $page,
        GameControllerInterface $game
    ): void {
        $page->setVar(
            'USER_COLONIES',
            ($user->getId() === UserEnum::USER_NOONE) ? [] : $user->getColonies()
        );
    }
}
