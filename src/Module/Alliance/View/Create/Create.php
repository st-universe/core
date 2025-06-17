<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Create;

use Override;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class Create implements ViewControllerInterface
{
    /**
     * @var string
     */
    public const string VIEW_IDENTIFIER = 'CREATE_ALLIANCE';

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        if ($user->getAlliance() !== null) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Allianz gründen'));
        $game->appendNavigationPart('alliance.php?showlist=1', _('Allianzliste'));
        $game->appendNavigationPart('alliance.php?CREATE_ALLIANCE=1', _('Allianz gründen'));
        $game->setViewTemplate('html/alliance/alliancecreate.twig');
    }
}
