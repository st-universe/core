<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Topic;

use Stu\Exception\AccessViolationException;
use Stu\Lib\Paging\PagingFactory;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceBoardPostRepositoryInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class Topic implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TOPIC';

    public const int ALLIANCEBOARDLIMITER = 20;

    public function __construct(
        private readonly TopicRequestInterface $topicRequest,
        private readonly AllianceBoardPostRepositoryInterface $allianceBoardPostRepository,
        private readonly AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository,
        private readonly PagingFactory $pagingFactory
    ) {}

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $userId = $game->getUser()->getId();
        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException("user not in alliance");
        }

        $topicId = $this->topicRequest->getTopicId();
        $allianceId = $alliance->getId();

        $topic = $this->allianceBoardTopicRepository->find($topicId);
        if ($topic === null) {
            throw new AccessViolationException(sprintf(_('userId %d tried to access non-existent topicId %d'), $userId, $topicId));
        }

        if ($topic->getAlliance()->getId() !== $alliance->getId()) {
            throw new AccessViolationException(sprintf(_('userId %d tried to access topic of foreign ally, topicId %d'), $userId, $topicId));
        }

        $boardId = $topic->getBoardId();

        $game->setPageTitle(_('Allianzforum'));

        $game->appendNavigationPart(
            sprintf('alliance.php?id=%d', $allianceId),
            _('Allianz')
        );
        $game->appendNavigationPart(
            'alliance.php?SHOW_BOARDS=1',
            _('Forum')
        );
        $game->appendNavigationPart(
            sprintf(
                'alliance.php?SHOW_BOARD=1&boardid=%d&id=%d',
                $boardId,
                $allianceId
            ),
            $topic->getBoard()->getName()
        );
        $game->appendNavigationPart(
            sprintf(
                'alliance.php?SHOW_TOPIC=1&boardid=%d&topicid=%d',
                $boardId,
                $topicId
            ),
            $topic->getName()
        );

        $game->setViewTemplate('html/alliance/allianceboardtopic.twig');
        $game->setTemplateVar('TOPIC', $topic);
        $game->setTemplateVar('PAGING', $this->pagingFactory->createPaging(
            $topic->getPostCount(),
            self::ALLIANCEBOARDLIMITER,
            $this->topicRequest->getPageMark(),
            sprintf('?SHOW_TOPIC=1&boardid=%d&topicid=%d', $boardId, $topicId)
        ));
        $game->setTemplateVar(
            'POSTINGS',
            $this->allianceBoardPostRepository->getByTopic(
                $topic->getId(),
                self::ALLIANCEBOARDLIMITER,
                $this->topicRequest->getPageMark()
            )
        );
        $game->setTemplateVar('USERID', $game->getUser()->getId());
    }
}
