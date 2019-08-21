<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\UnsetTopicSticky;

use AccessViolation;
use AllianceTopic;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Topic\Topic;

final class UnsetTopicSticky implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_UNSET_STICKY';

    private $unsetTopicStickyRequest;

    public function __construct(
        UnsetTopicStickyRequestInterface $unsetTopicStickyRequest
    ) {
        $this->unsetTopicStickyRequest = $unsetTopicStickyRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $topic = new AllianceTopic($this->unsetTopicStickyRequest->getTopicId());
        if ($topic->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $topic->setSticky(0);
        $topic->save();

        $game->addInformation(_('Die Markierung des Themas wurde entfernt'));

        $game->setView(Topic::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
