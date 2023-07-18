<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\AddContact;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AddContact implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ADD_CONTACT';

    private AddContactRequestInterface $addContactRequest;

    private ContactRepositoryInterface $contactRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        AddContactRequestInterface $addContactRequest,
        ContactRepositoryInterface $contactRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        UserRepositoryInterface $userRepository
    ) {
        $this->addContactRequest = $addContactRequest;
        $this->contactRepository = $contactRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateVar('div', $this->addContactRequest->getContactDiv());
        $game->setTemplateVar('contact', null);

        $userId = $game->getUser()->getId();

        $recipiendIdString = trim($this->addContactRequest->getRecipientId());

        if (!is_numeric($recipiendIdString) || ((int)$recipiendIdString) < 1) {
            $game->addInformation(_("Ungültiger Wert angegeben. Muss positive Zahl sein!"));
            return;
        }

        $recipient = $this->userRepository->find((int)$recipiendIdString);
        if ($recipient === null) {
            $game->addInformation(_('Dieser Spieler existiert nicht'));
            return;
        }
        if ($recipient->isContactable() === false) {
            $game->addInformation(_('Dieser Spieler kann nicht hinzugefügt werden'));
            return;
        }
        if ($recipient->getId() === $userId) {
            $game->addInformation(_('Du kannst Dich nicht selbst auf die Kontaktliste setzen'));
            return;
        }
        if ($this->contactRepository->getByUserAndOpponent($userId, $recipient->getId()) !== null) {
            $game->addInformation(_('Dieser Spieler befindet sich bereits auf Deiner Kontaktliste'));
            return;
        }

        $mode = $this->addContactRequest->getModeId();

        if (!in_array($mode, ContactListModeEnum::AVAILABLE_MODES)) {
            return;
        }
        $contact = $this->contactRepository->prototype();
        $contact->setUser($game->getUser());
        $contact->setMode($mode);
        $contact->setRecipient($recipient);
        $contact->setDate(time());

        $this->contactRepository->save($contact);

        if ($mode == ContactListModeEnum::CONTACT_ENEMY) {
            $this->privateMessageSender->send(
                $userId,
                $recipient->getId(),
                _('Der Spieler betrachtet Dich von nun an als Feind')
            );
        }
        $game->addInformation(_('Der Spieler wurde hinzugefügt'));

        $game->setTemplateVar('contact', $contact);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
