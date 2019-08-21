<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateTopic;

use AccessViolation;
use AllianceBoard;
use AlliancePostData;
use AllianceTopicData;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Board\Board;

final class CreateTopic implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_CREATE_TOPIC';

    private $createTopicRequest;

    public function __construct(
        CreateTopicRequestInterface $createTopicRequest
    ) {
        $this->createTopicRequest = $createTopicRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        $userId = $game->getUser()->getId();

        $name = $this->createTopicRequest->getTopicTitle();
        $text = $this->createTopicRequest->getText();
        $boardId = $this->createTopicRequest->getBoardId();

        if (mb_strlen($name) < 1) {
            $game->setView("SHOW_NEW_TOPIC");
            $game->addInformation(_('Es wurde kein Themenname eingegeben'));
            return;
        }
        if (mb_strlen($text) < 1) {
            $game->setView("SHOW_NEW_TOPIC");
            $game->addInformation(_('Es wurde kein Text eingegeben'));
            return;
        }

        $post = new AlliancePostData();
        $post->setText($text);
        $post->setName($name);

        $board = new AllianceBoard($boardId);
        if ($board->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $date = time();

        $topic = new AllianceTopicData();
        $topic->setBoardId($board->getId());
        $topic->setAllianceId($alliance->getId());
        $topic->setName($name);
        $topic->setUserId($userId);
        $topic->setLastPostDate($date);
        $topic->save();

        $post->setBoardId($board->getId());
        $post->setTopicId($topic->getId());
        $post->setAllianceId($alliance->getId());
        $post->setUserId($userId);
        $post->setDate($date);
        $post->save();

        $game->setView(Board::VIEW_IDENTIFIER);

        $game->addInformation(_('Das Thema wurde erstellt'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
