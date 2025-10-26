<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowContactList;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Orm\Repository\ContactRepositoryInterface;

final class ShowContactList implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_CONTACTLIST';

    public function __construct(private ContactRepositoryInterface $contactRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $game->setViewTemplate('html/user/contactList.twig');
        $game->appendNavigationPart(
            sprintf('pm.php?%s=1', self::VIEW_IDENTIFIER),
            'Kontaktliste'
        );
        $game->setPageTitle('Kontaktliste');

        $game->setTemplateVar('CONTACT_LIST', $this->contactRepository->getOrderedByUser($user));
        $game->setTemplateVar('REMOTE_CONTACTS', $this->contactRepository->getRemoteOrderedByUser($user));
        $game->setTemplateVar('CONTACT_LIST_MODES', ContactListModeEnum::cases());
    }
}
