<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddContact;

use PM;
use Stu\Module\Communication\Lib\ContactListModeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use User;

final class AddContact implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ADD_CONTACT';

    private $addContactRequest;

    private $contactRepository;

    public function __construct(
        AddContactRequestInterface $addContactRequest,
        ContactRepositoryInterface $contactRepository
    ) {
        $this->addContactRequest = $addContactRequest;
        $this->contactRepository = $contactRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateVar('div', $this->addContactRequest->getContactDiv());

        $userId = $game->getUser()->getId();

        $recipient = User::getUserById($this->addContactRequest->getRecipientId());
        if (!$recipient) {
            $game->addInformation(_('Dieser Spieler existiert nicht'));
            return;
        }
        if (isSystemUser($recipient->getId())) {
            $game->addInformation(_('Dieser Spieler kann nicht hinzugefügt werden'));
            return;
        }
        if ($recipient->getId() == $userId) {
            $game->addInformation(_('Du kannst Dich nicht selbst auf die Kontaktliste setzen'));
            return;
        }
        if ($this->contactRepository->getByUserAndOpponent($userId, $recipient->getId()) !== null) {
            $game->addInformation(_('Dieser Spieler befindet sich bereits auf Deiner Kontaktliste'));
            return;
        }

        $mode = $this->addContactRequest->getModeId();

        if (!array_key_exists($mode, getContactlistModes())) {
            return;
        }
        $contact = $this->contactRepository->prototype();
        $contact->setUserId($userId);
        $contact->setMode($mode);
        $contact->setRecipientId($recipient->getId());
        $contact->setDate(time());

        $this->contactRepository->save($contact);

        if ($mode == ContactListModeEnum::CONTACT_ENEMY) {
            PM::sendPM($userId, $recipient->getId(), _('Der Spieler betrachtet Dich von nun an als Feind'));
        }
        $game->addInformation(_('Der Spieler wurde hinzugefügt'));

        $game->setTemplateVar('contact', $contact);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
