<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Topic;

use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\AllianceBoardTopic;
use Stu\Orm\Repository\AllianceBoardPostRepositoryInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class Topic implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TOPIC';

    public const int ALLIANCEBOARDLIMITER = 20;

    public function __construct(
        private TopicRequestInterface $topicRequest,
        private AllianceBoardPostRepositoryInterface $allianceBoardPostRepository,
        private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository,
        private AllianceActionManagerInterface $allianceActionManager
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
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

        if ($topic->getAlliance() !== $alliance) {
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
        $game->setTemplateVar('TOPIC_NAVIGATION', $this->getTopicNavigation($topic));
        $game->setTemplateVar(
            'POSTINGS',
            $this->allianceBoardPostRepository->getByTopic(
                $topic->getId(),
                self::ALLIANCEBOARDLIMITER,
                $this->topicRequest->getPageMark()
            )
        );
        $game->setTemplateVar(
            'IS_MODERATOR',
            $this->allianceActionManager->mayEdit($alliance, $game->getUser())
        );
        $game->setTemplateVar('USERID', $game->getUser()->getId());
    }

    /** @return array< array{page: '<'|'<<'|'>', mark: int<-20, max>, cssclass: 'pages'}|array{page: '>>'|float, mark: float, cssclass: 'pages'|'pages selected'}> */
    private function getTopicNavigation(AllianceBoardTopic $topic): array
    {
        $mark = $this->topicRequest->getPageMark();
        if ($mark % self::ALLIANCEBOARDLIMITER != 0 || $mark < 0) {
            $mark = 0;
        }

        $maxcount = $topic->getPostCount();
        $maxpage = ceil($maxcount / self::ALLIANCEBOARDLIMITER);
        $curpage = floor($mark / self::ALLIANCEBOARDLIMITER);
        $ret = [];
        if ($curpage != 0) {
            $ret[] = ["page" => "<<", "mark" => 0, "cssclass" => "pages"];
            $ret[] = ["page" => "<", "mark" => ($mark - self::ALLIANCEBOARDLIMITER), "cssclass" => "pages"];
        }

        for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
            if ($i > $maxpage || $i < 1) {
                continue;
            }

            $ret[] = ["page" => $i, "mark" => ($i * self::ALLIANCEBOARDLIMITER - self::ALLIANCEBOARDLIMITER), "cssclass" => ($curpage + 1 === $i ? "pages selected" : "pages")];
        }

        if ($curpage + 1 !== $maxpage) {
            $ret[] = ["page" => ">", "mark" => ($mark + self::ALLIANCEBOARDLIMITER), "cssclass" => "pages"];
            $ret[] = ["page" => ">>", "mark" => $maxpage * self::ALLIANCEBOARDLIMITER - self::ALLIANCEBOARDLIMITER, "cssclass" => "pages"];
        }

        return $ret;
    }
}
