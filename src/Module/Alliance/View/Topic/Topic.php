<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Topic;

use Override;
use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\AllianceBoardTopicInterface;
use Stu\Orm\Repository\AllianceBoardPostRepositoryInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class Topic implements ViewControllerInterface
{
    /**
     * @var string
     */
    public const string VIEW_IDENTIFIER = 'SHOW_TOPIC';

    /**
     * @var int
     */
    public const int ALLIANCEBOARDLIMITER = 20;

    public function __construct(private TopicRequestInterface $topicRequest, private AllianceBoardPostRepositoryInterface $allianceBoardPostRepository, private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository, private AllianceActionManagerInterface $allianceActionManager)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolation();
        }

        $topicId = $this->topicRequest->getTopicId();
        $allianceId = $alliance->getId();

        /** @var AllianceBoardTopicInterface $topic */
        $topic = $this->allianceBoardTopicRepository->find($topicId);
        if ($topic === null) {
            throw new AccessViolation(sprintf(_('userId %d tried to access non-existent topicId %d'), $userId, $topicId));
        }

        if ($topic->getAllianceId() !== $allianceId) {
            throw new AccessViolation(sprintf(_('userId %d tried to access topic of foreign ally, topicId %d'), $userId, $topicId));
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
                'alliance.php?SHOW_BOARD=1&bid=%d&id=%d',
                $boardId,
                $allianceId
            ),
            $topic->getBoard()->getName()
        );
        $game->appendNavigationPart(
            sprintf(
                'alliance.php?SHOW_TOPIC=1&bid=%d&tid=%d',
                $boardId,
                $topicId
            ),
            $topic->getName()
        );

        $game->setTemplateFile('html/allianceboardtopic.xhtml');
        $game->setTemplateVar('TOPIC', $topic);
        $game->setTemplateVar('TOPIC_NAVIGATION', $this->getTopicNavigation($topic));
        $game->setTemplateVar(
            'POSTINGS',
            $this->allianceBoardPostRepository->getByTopic(
                $topic->getId(),
                static::ALLIANCEBOARDLIMITER,
                $this->topicRequest->getPageMark()
            )
        );
        $game->setTemplateVar(
            'IS_MODERATOR',
            $this->allianceActionManager->mayEdit($alliance, $game->getUser())
        );
        $game->setTemplateVar('USERID', $game->getUser()->getId());
    }

    private function getTopicNavigation(AllianceBoardTopicInterface $topic): array
    {
        $mark = $this->topicRequest->getPageMark();
        if ($mark % static::ALLIANCEBOARDLIMITER != 0 || $mark < 0) {
            $mark = 0;
        }

        $maxcount = $topic->getPostCount();
        $maxpage = ceil($maxcount / static::ALLIANCEBOARDLIMITER);
        $curpage = floor($mark / static::ALLIANCEBOARDLIMITER);
        $ret = [];
        if ($curpage != 0) {
            $ret[] = ["page" => "<<", "mark" => 0, "cssclass" => "pages"];
            $ret[] = ["page" => "<", "mark" => ($mark - static::ALLIANCEBOARDLIMITER), "cssclass" => "pages"];
        }

        for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
            if ($i > $maxpage || $i < 1) {
                continue;
            }

            $ret[] = ["page" => $i, "mark" => ($i * static::ALLIANCEBOARDLIMITER - static::ALLIANCEBOARDLIMITER), "cssclass" => ($curpage + 1 === $i ? "pages selected" : "pages")];
        }

        if ($curpage + 1 !== $maxpage) {
            $ret[] = ["page" => ">", "mark" => ($mark + static::ALLIANCEBOARDLIMITER), "cssclass" => "pages"];
            $ret[] = ["page" => ">>", "mark" => $maxpage * static::ALLIANCEBOARDLIMITER - static::ALLIANCEBOARDLIMITER, "cssclass" => "pages"];
        }

        return $ret;
    }
}
