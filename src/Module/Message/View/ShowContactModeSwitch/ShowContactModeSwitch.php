<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowContactModeSwitch;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Orm\Repository\ContactRepositoryInterface;

final class ShowContactModeSwitch implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_CONTACT_MODESWITCH';

    public function __construct(private ShowContactModeSwitchRequestInterface $showContactModeSwitchRequest, private ContactRepositoryInterface $contactRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $contact = $this->contactRepository->find($this->showContactModeSwitchRequest->getContactId());

        if ($contact === null || $contact->getUserId() !== $game->getUser()->getId()) {
            return;
        }

        $game->setPageTitle(_('Status'));
        $game->setMacroInAjaxWindow('html/user/contactModeSwitch.twig');
        $game->setTemplateVar('contact', $contact);
        $game->setTemplateVar('CONTACT_LIST_MODES', ContactListModeEnum::cases());
    }
}
