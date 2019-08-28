<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SetTopicSticky;

use AccessViolation;
use AllianceTopic;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Topic\Topic;

final class SetTopicSticky implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_SET_STICKY';

    private $setTopicStickyRequest;

    public function __construct(
        SetTopicStickyRequestInterface $setTopicStickyRequest
    ) {
        $this->setTopicStickyRequest = $setTopicStickyRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $topic = new AllianceTopic($this->setTopicStickyRequest->getTopicId());
        if ($topic->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $topic->setSticky(1);
        $topic->save();

        $game->addInformation(_('Das Thema wurde als wichtig markiert'));

        $game->setView(Topic::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
