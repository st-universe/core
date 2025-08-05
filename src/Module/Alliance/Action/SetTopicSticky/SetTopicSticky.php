<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SetTopicSticky;

use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\View\Topic\Topic;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\AllianceBoardTopic;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class SetTopicSticky implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_STICKY';

    public function __construct(
        private SetTopicStickyRequestInterface $setTopicStickyRequest,
        private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $topic = $this->allianceBoardTopicRepository->find($this->setTopicStickyRequest->getTopicId());
        if ($topic === null || $topic->getAlliance() !== $alliance) {
            throw new AccessViolationException();
        }

        $topic->setSticky(true);

        $this->allianceBoardTopicRepository->save($topic);

        $game->getInfo()->addInformation(_('Das Thema wurde als wichtig markiert'));

        $game->setView(Topic::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
