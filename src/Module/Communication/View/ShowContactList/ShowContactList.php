<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowContactList;

use Contactlist;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowContactList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_CONTACTLIST';

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $game->setTemplateFile('html/contactlist.xhtml');
        $game->appendNavigationPart(
            sprintf('comm.php?%s=1', static::VIEW_IDENTIFIER),
            _('Kontaktliste')
        );
        $game->setPageTitle(_('Kontaktliste'));

        $game->setTemplateVar('CONTACT_LIST', Contactlist::getList($userId));
        $game->setTemplateVar('REMOTE_CONTACTS', Contactlist::getRemoteContacts($userId));
    }
}
