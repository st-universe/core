<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Topic;

use AccessViolation;
use AllianceBoard;
use AllianceTopic;
use AllianceTopicData;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class Topic implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TOPIC';

    public const ALLIANCEBOARDLIMITER = 20;

    private $topicRequest;

    public function __construct(
        TopicRequestInterface $topicRequest
    ) {
        $this->topicRequest = $topicRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $boardId = $this->topicRequest->getBoardId();
        $topicId = $this->topicRequest->getTopicId();
        $allianceId = $alliance->getId();
        $board = new AllianceBoard($boardId);

        if ($board->getAllianceId() != $allianceId) {
            throw new AccessViolation();
        }

        $topic = new AllianceTopic($topicId);
        if ($topic->getAllianceId() != $allianceId) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Allianzforum'));

        $game->appendNavigationPart(
            sprintf('alliance.php?SHOW_ALLIANCE=1&id=%d', $allianceId),
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
            $board->getName()
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
        $game->setTemplateVar('POSTINGS', $topic->getPostings($this->topicRequest->getPageMark()));
        $game->setTemplateVar('IS_MODERATOR', $alliance->currentUserIsBoardModerator());
    }

    private function getTopicNavigation(AllianceTopicData $topic): array
    {
        $mark = $this->topicRequest->getPageMark();
        if ($mark % static::ALLIANCEBOARDLIMITER != 0 || $mark < 0) {
            $mark = 0;
        }
        $maxcount = $topic->getPostCount();
        $maxpage = ceil($maxcount / static::ALLIANCEBOARDLIMITER);
        $curpage = floor($mark / static::ALLIANCEBOARDLIMITER);
        $ret = array();
        if ($curpage != 0) {
            $ret[] = array("page" => "<<", "mark" => 0, "cssclass" => "pages");
            $ret[] = array("page" => "<", "mark" => ($mark - static::ALLIANCEBOARDLIMITER), "cssclass" => "pages");
        }
        for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
            if ($i > $maxpage || $i < 1) {
                continue;
            }
            $ret[] = array(
                "page" => $i,
                "mark" => ($i * static::ALLIANCEBOARDLIMITER - static::ALLIANCEBOARDLIMITER),
                "cssclass" => ($curpage + 1 == $i ? "pages selected" : "pages")
            );
        }
        if ($curpage + 1 != $maxpage) {
            $ret[] = array("page" => ">", "mark" => ($mark + static::ALLIANCEBOARDLIMITER), "cssclass" => "pages");
            $ret[] = array(
                "page" => ">>",
                "mark" => $maxpage * static::ALLIANCEBOARDLIMITER - static::ALLIANCEBOARDLIMITER,
                "cssclass" => "pages"
            );
        }
        return $ret;
    }
}
