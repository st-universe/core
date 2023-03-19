<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Stu\Module\Tal\TalPageInterface;
use Stu\Orm\Entity\UserInterface;

interface RenderFragmentInterface
{
    public function render(
        UserInterface $user,
        TalPageInterface $talPage
    ): void;
}
