<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SetTopicSticky;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Topic\Topic;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class SetTopicSticky implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SET_STICKY';

    private $setTopicStickyRequest;

    private $allianceBoardTopicRepository;

    public function __construct(
        SetTopicStickyRequestInterface $setTopicStickyRequest,
        AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository
    ) {
        $this->setTopicStickyRequest = $setTopicStickyRequest;
        $this->allianceBoardTopicRepository = $allianceBoardTopicRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $topic = $this->allianceBoardTopicRepository->find($this->setTopicStickyRequest->getTopicId());
        if ($topic === null || $topic->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $topic->setSticky(true);

        $this->allianceBoardTopicRepository->save($topic);

        $game->addInformation(_('Das Thema wurde als wichtig markiert'));

        $game->setView(Topic::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
