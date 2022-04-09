<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ShowInvitationTree;

use Fhaculty\Graph\Graph;
use Graphp\GraphViz\GraphViz;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\UserInvitationRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShowInvitationTree implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_INVITATION_TREE';

    private UserRepositoryInterface $userRepository;

    private UserInvitationRepositoryInterface $userInvitationRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        UserInvitationRepositoryInterface $userInvitationRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userInvitationRepository = $userInvitationRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        // only Admins can show it
        if (!$game->getUser()->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $graph = new Graph();
        $vertexes = [];

        $userList = $this->userRepository->getNonNpcList();
        foreach ($userList as $user) {
            $vertex = $graph->createVertex($user->getId());
            $vertex->setAttribute('graphviz.label', $user->getName());
            $vertexes[$user->getId()] = $vertex;
        }

        $invitations = $this->userInvitationRepository->findAll();
        foreach ($invitations as $invitation) {
            $userId = $invitation->getUserId();
            $invitedUserId = $invitation->getInvitedUserId();
            if (!array_key_exists($userId, $vertexes) || !array_key_exists($invitedUserId, $vertexes)) {
                continue;
            }
            $vertexes[$userId]->createEdgeTo($vertexes[$invitedUserId]);
        }

        $graphviz = new GraphViz();

        $game->appendNavigationPart(
            sprintf(
                '/admin/?%s=1',
                static::VIEW_IDENTIFIER
            ),
            _('Einladungsbaum')
        );
        $game->setTemplateFile('html/admin/tree.xhtml');
        $game->setPageTitle(_('Forschungsbaum'));
        $game->setTemplateVar('TREE',   $graphviz->createImageHtml($graph));
    }
}
