<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\RenameTopic;

use AccessViolation;
use AllianceTopic;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Board\Board;

final class RenameTopic implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_RENAME_TOPIC';

    private $renameTopicRequest;

    public function __construct(
        RenameTopicRequestInterface $renameTopicRequest
    ) {
        $this->renameTopicRequest = $renameTopicRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $name = $this->renameTopicRequest->getTitle();
        $topicId = $this->renameTopicRequest->getTopicId();

        $topic = new AllianceTopic($topicId);
        if ($topic->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $game->setView(Board::VIEW_IDENTIFIER);

        if (mb_strlen($name) < 1) {
            $game->addInformation(_('Es wurde kein Themenname eingegeben'));
            return;
        }

        $topic->setName($name);
        $topic->save();

        $game->addInformation(_('Das Thema wurde umbenannt'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
