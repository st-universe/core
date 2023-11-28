<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowContactList;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;

final class ShowContactList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_CONTACTLIST';
    private ContactRepositoryInterface $contactRepository;

    public function __construct(
        ContactRepositoryInterface $contactRepository
    ) {
        $this->contactRepository = $contactRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $game->setViewTemplate('html/user/contactList.twig');
        $game->appendNavigationPart(
            sprintf('pm.php?%s=1', static::VIEW_IDENTIFIER),
            _('Kontaktliste')
        );
        $game->setPageTitle(_('Kontaktliste'));

        $game->setTemplateVar('CONTACT_LIST', $this->contactRepository->getOrderedByUser($userId));
        $game->setTemplateVar('REMOTE_CONTACTS', $this->contactRepository->getRemoteOrderedByUser($userId));
    }
}
