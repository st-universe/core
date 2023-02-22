<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowContactModeSwitch5;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Message\View\ShowContactModeSwitch\ShowContactModeSwitchRequestInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;

final class ShowContactModeSwitch implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_CONTACT_MODESWITCH';

    private ShowContactModeSwitchRequestInterface $showContactModeSwitchRequest;

    private ContactRepositoryInterface $contactRepository;

    public function __construct(
        ShowContactModeSwitchRequestInterface $showContactModeSwitchRequest,
        ContactRepositoryInterface $contactRepository
    ) {
        $this->showContactModeSwitchRequest = $showContactModeSwitchRequest;
        $this->contactRepository = $contactRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $contact = $this->contactRepository->find($this->showContactModeSwitchRequest->getContactId());

        if ($contact === null || $contact->getUserId() != $game->getUser()->getId()) {
            return;
        }
        $game->setPageTitle(_('Status'));
        $game->setMacroInAjaxWindow('html/commmacros.xhtml/clmodeswitch');
        $game->setTemplateVar('contact', $contact);
    }
}
