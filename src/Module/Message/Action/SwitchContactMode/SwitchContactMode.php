<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\SwitchContactMode;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Message\View\ShowContactMode\ShowContactMode;
use Stu\Orm\Repository\ContactRepositoryInterface;

final class SwitchContactMode implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_CONTACTMODE';

    private SwitchContactModeRequestInterface $switchContactModeRequest;

    private ContactRepositoryInterface $contactRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        SwitchContactModeRequestInterface $switchContactModeRequest,
        ContactRepositoryInterface $contactRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->switchContactModeRequest = $switchContactModeRequest;
        $this->contactRepository = $contactRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateVar('div', $this->switchContactModeRequest->getContactDiv());

        $contact = $this->contactRepository->find($this->switchContactModeRequest->getContactId());
        $mode = ContactListModeEnum::tryFrom($this->switchContactModeRequest->getModeId());
        $userId = $game->getUser()->getId();

        if ($contact === null || $contact->getUserId() !== $userId) {
            return;
        }
        if ($mode === null) {
            return;
        }
        if ($mode !== $contact->getMode() && $mode == ContactListModeEnum::ENEMY) {
            $this->privateMessageSender->send(
                $userId,
                $contact->getRecipientId(),
                _('Der Siedler betrachtet Dich von nun an als Feind')
            );
            $obj = $this->contactRepository->getByUserAndOpponent(
                $contact->getRecipientId(),
                $userId
            );
            if ($obj !== null) {
                if (!$obj->isEnemy()) {
                    $obj->setMode(ContactListModeEnum::ENEMY);

                    $this->contactRepository->save($obj);
                }
            } else {
                $obj = $this->contactRepository->prototype();
                $obj->setUser($contact->getRecipient());
                $obj->setRecipient($game->getUser());
                $obj->setMode(ContactListModeEnum::ENEMY);
                $obj->setDate(time());

                $this->contactRepository->save($obj);
            }
        }
        $contact->setMode($mode);

        $this->contactRepository->save($contact);

        $game->setTemplateVar('contact', $contact);
        $game->setView(ShowContactMode::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
