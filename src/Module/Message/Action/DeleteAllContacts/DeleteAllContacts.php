<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteAllContacts;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\View\ShowContactList\ShowContactList;
use Stu\Orm\Repository\ContactRepositoryInterface;

final class DeleteAllContacts implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_ALL_CONTACTS';

    public function __construct(private ContactRepositoryInterface $contactRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowContactList::VIEW_IDENTIFIER);

        $this->contactRepository->truncateByUser($game->getUser()->getId());

        $game->getInfo()->addInformation(_('Die Kontakte wurden gelöscht'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
