<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteTopic;

use AccessViolation;
use AllianceTopic;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Board\Board;

final class DeleteTopic implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_DELETE_TOPIC';

    private $deleteTopicRequest;

    public function __construct(
        DeleteTopicRequestInterface $deleteTopicRequest
    ) {
        $this->deleteTopicRequest = $deleteTopicRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $topic = new AllianceTopic($this->deleteTopicRequest->getTopicId());
        if ($topic->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $topic->deepDelete();

        $game->addInformation(_('Das Thema wurde gelöscht'));

        $game->setView(Board::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
