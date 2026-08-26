<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowContactList;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\UserRelationRepositoryInterface;

final class ShowContactList implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_CONTACTLIST';

    public function __construct(
        private ContactRepositoryInterface $contactRepository,
        private UserRelationRepositoryInterface $userRelationRepository,
        private UserRelationManagerInterface $userRelationManager
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $showUserRelations = $user->getAlliance() === null;

        $game->setViewTemplate('html/user/contactList.twig');
        $game->appendNavigationPart(
            sprintf('pm.php?%s=1', self::VIEW_IDENTIFIER),
            'Kontaktliste'
        );
        $game->setPageTitle('Kontaktliste');

        $game->setTemplateVar('CONTACT_LIST', $this->contactRepository->getOrderedByUser($user));
        $game->setTemplateVar('REMOTE_CONTACTS', $this->contactRepository->getRemoteOrderedByUser($user));
        $game->setTemplateVar('CONTACT_LIST_MODES', ContactListModeEnum::cases());
        $game->setTemplateVar('SHOW_USER_RELATIONS', $showUserRelations);
        $game->setTemplateVar('USER_RELATIONS', $showUserRelations ? $this->userRelationRepository->getByUserAndAlliance($user, null) : []);
        $game->setTemplateVar('CAN_MANAGE_USER_RELATIONS', $showUserRelations && $this->userRelationManager->canManageRelations($user));
        $game->setTemplateVar('CAN_CREATE_USER_RELATIONS', $showUserRelations && $this->userRelationManager->getRepresentedParty($user) !== null);
        $game->setTemplateVar('POSSIBLE_USER_RELATION_TYPES', [
            AllianceRelationTypeEnum::WAR,
            AllianceRelationTypeEnum::FRIENDS,
            AllianceRelationTypeEnum::ALLIED,
            AllianceRelationTypeEnum::TRADE,
            AllianceRelationTypeEnum::VASSAL,
        ]);
    }
}
