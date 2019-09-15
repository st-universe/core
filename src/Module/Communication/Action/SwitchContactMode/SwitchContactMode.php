<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\SwitchContactMode;

use PM;
use Stu\Module\Communication\Lib\ContactListModeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Communication\View\ShowContactMode\ShowContactMode;
use Stu\Orm\Repository\ContactRepositoryInterface;

final class SwitchContactMode implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_CONTACTMODE';

    private $switchContactModeRequest;

    private $contactRepository;

    public function __construct(
        SwitchContactModeRequestInterface $switchContactModeRequest,
        ContactRepositoryInterface $contactRepository
    ) {
        $this->switchContactModeRequest = $switchContactModeRequest;
        $this->contactRepository = $contactRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateVar('div', $this->switchContactModeRequest->getContactDiv());

        $contact = $this->contactRepository->find($this->switchContactModeRequest->getContactId());
        $mode = $this->switchContactModeRequest->getModeId();
        $userId = $game->getUser()->getId();

        if ($contact === null || $contact->getUserId() != $userId) {
            return;
        }
        if (!array_key_exists($mode, getContactlistModes())) {
            return;
        }
        if ($mode != $contact->getMode() && $mode == ContactListModeEnum::CONTACT_ENEMY) {
            PM::sendPM(
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
                    $obj->setMode(ContactListModeEnum::CONTACT_ENEMY);

                    $this->contactRepository->save($obj);
                }
            } else {
                $obj = $this->contactRepository->prototype();
                $obj->setUserId($contact->getRecipientId());
                $obj->setRecipientId($userId);
                $obj->setMode(ContactListModeEnum::CONTACT_ENEMY);
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
